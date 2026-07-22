import './bootstrap';
import axios from 'axios';

let mode = 'release';
let borrowing = null;
let scanLocked = false;
let sessionCompleted = false;
let confirmationModal = null;

const scannedUnits = new Map();

const borrowingPanel =
    document.getElementById('borrowingPanel');

const equipmentList =
    document.getElementById('equipmentList');

const progress =
    document.getElementById('progress');

const status =
    document.getElementById('scannerStatus');

const releaseModeButton =
    document.getElementById('releaseMode');

const returnModeButton =
    document.getElementById('returnMode');

function escapeHtml(value) {
    return String(value ?? '')
        .replaceAll('&', '&amp;')
        .replaceAll('<', '&lt;')
        .replaceAll('>', '&gt;')
        .replaceAll('"', '&quot;')
        .replaceAll("'", '&#039;');
}

function getErrorMessage(
    error,
    fallback = 'Something went wrong.',
) {
    const response =
        error?.response?.data;

    if (response?.message) {
        return response.message;
    }

    if (response?.errors) {
        const firstError =
            Object.values(response.errors)
                .flat()
                .find(Boolean);

        if (firstError) {
            return firstError;
        }
    }

    return fallback;
}

function showMessage(
    message,
    type = 'info',
) {
    if (!status) {
        return;
    }

    const classes = {
        success: [
            'text-green-700',
            'bg-green-50',
            'border-green-200',
        ],

        error: [
            'text-red-700',
            'bg-red-50',
            'border-red-200',
        ],

        warning: [
            'text-amber-700',
            'bg-amber-50',
            'border-amber-200',
        ],

        info: [
            'text-slate-700',
            'bg-slate-50',
            'border-slate-200',
        ],
    };

    status.textContent = message;

    status.className =
        'rounded-lg border px-4 py-3 text-sm font-medium';

    status.classList.add(
        ...(classes[type] ?? classes.info),
    );
}

function setModeButtonState() {
    const activeClasses = [
        'bg-blue-600',
        'text-white',
        'shadow-sm',
    ];

    const inactiveClasses = [
        'bg-white',
        'text-slate-700',
        'border',
        'border-slate-300',
    ];

    [
        releaseModeButton,
        returnModeButton,
    ].forEach((button) => {
        if (!button) {
            return;
        }

        button.classList.remove(
            ...activeClasses,
            ...inactiveClasses,
        );
    });

    if (mode === 'release') {
        releaseModeButton?.classList.add(
            ...activeClasses,
        );

        returnModeButton?.classList.add(
            ...inactiveClasses,
        );
    } else {
        returnModeButton?.classList.add(
            ...activeClasses,
        );

        releaseModeButton?.classList.add(
            ...inactiveClasses,
        );
    }
}

function removeActionButton() {
    document
        .getElementById('scannerActionButton')
        ?.remove();
}

function closeConfirmationModal() {
    confirmationModal?.remove();

    confirmationModal = null;

    document.body.classList.remove(
        'overflow-hidden',
    );
}

function resetSession() {
    borrowing = null;
    scanLocked = false;
    sessionCompleted = false;

    scannedUnits.clear();

    closeConfirmationModal();
    removeActionButton();
    setModeButtonState();

    if (borrowingPanel) {
        borrowingPanel.innerHTML = `
            <p class="text-slate-400">
                No borrowing loaded.
            </p>
        `;
    }

    if (equipmentList) {
        equipmentList.innerHTML = `
            <div class="rounded-xl border border-dashed border-slate-300 p-6 text-center text-sm text-slate-400">
                Equipment will appear after scanning a borrowing QR code.
            </div>
        `;
    }

    if (progress) {
        progress.textContent = '0 / 0';
    }

    showMessage(
        'Waiting for Borrowing QR...',
        'info',
    );
}

function isBorrowingValidForMode(record) {
    if (mode === 'release') {
        return record.status === 'approved';
    }

    return [
        'released',
        'overdue',
    ].includes(record.status);
}

function modeStatusError(record) {
    if (mode === 'release') {
        return `This borrowing has status “${record.status}”. Only approved borrowings can be released.`;
    }

    return `This borrowing has status “${record.status}”. Only released or overdue borrowings can be returned.`;
}

async function loadBorrowing(code) {
    closeConfirmationModal();
    removeActionButton();

    try {
        showMessage(
            'Checking borrowing record...',
            'info',
        );

        const response = await axios.post(
            '/scanner/borrowing',
            {
                code: String(code).trim(),
            },
        );

        const record =
            response.data.borrowing;

        if (
            !isBorrowingValidForMode(record)
        ) {
            borrowing = null;

            scannedUnits.clear();

            showMessage(
                modeStatusError(record),
                'error',
            );

            return;
        }

        borrowing = record;

        scannedUnits.clear();

        sessionCompleted = false;

        renderBorrowing();
        renderItems();
        prepareAssignedUnits();

        showMessage(
            'Borrowing loaded. Review the information before confirming.',
            'success',
        );

        openConfirmationModal();
    } catch (error) {
        borrowing = null;

        scannedUnits.clear();

        showMessage(
            getErrorMessage(
                error,
                'Borrowing record not found.',
            ),
            'error',
        );
    }
}

function renderBorrowing() {
    if (
        !borrowingPanel
        || !borrowing
    ) {
        return;
    }

    borrowingPanel.innerHTML = `
        <div class="grid gap-4 sm:grid-cols-2">
            <div>
                <p class="text-xs font-semibold uppercase tracking-wide text-slate-400">
                    Borrowing Code
                </p>

                <p class="mt-1 font-semibold text-slate-900">
                    ${escapeHtml(borrowing.code)}
                </p>
            </div>

            <div>
                <p class="text-xs font-semibold uppercase tracking-wide text-slate-400">
                    Borrower
                </p>

                <p class="mt-1 font-semibold text-slate-900">
                    ${escapeHtml(
                        borrowing.user?.name,
                    )}
                </p>
            </div>

            <div>
                <p class="text-xs font-semibold uppercase tracking-wide text-slate-400">
                    ID Number
                </p>

                <p class="mt-1 font-semibold text-slate-900">
                    ${escapeHtml(
                        borrowing.user?.id_number
                        || '—',
                    )}
                </p>
            </div>

            <div>
                <p class="text-xs font-semibold uppercase tracking-wide text-slate-400">
                    Current Status
                </p>

                <p class="mt-1 font-semibold capitalize text-slate-900">
                    ${escapeHtml(
                        borrowing.status,
                    )}
                </p>
            </div>

            <div class="sm:col-span-2">
                <p class="text-xs font-semibold uppercase tracking-wide text-slate-400">
                    Purpose
                </p>

                <p class="mt-1 text-sm text-slate-800">
                    ${escapeHtml(
                        borrowing.purpose || '—',
                    )}
                </p>
            </div>
        </div>
    `;
}

function renderItems() {
    if (
        !equipmentList
        || !borrowing
    ) {
        return;
    }

    if (!borrowing.items.length) {
        equipmentList.innerHTML = `
            <div class="rounded-xl border border-dashed border-slate-300 p-6 text-center text-sm text-slate-400">
                No equipment is assigned to this borrowing.
            </div>
        `;

        return;
    }

    equipmentList.innerHTML =
        borrowing.items
            .map(
                (item) => `
                    <div
                        id="item-unit-${Number(
                            item.item_unit_id,
                        )}"
                        class="rounded-xl border border-blue-200 bg-blue-50 p-4"
                    >
                        <div class="flex items-start justify-between gap-3">
                            <div class="min-w-0">
                                <p class="truncate font-semibold text-slate-900">
                                    ${escapeHtml(
                                        item.item_name,
                                    )}
                                </p>

                                <p class="text-sm text-slate-500">
                                    ${escapeHtml(
                                        item.category
                                        || 'Uncategorized',
                                    )}
                                </p>

                                <p class="mt-1 text-sm text-slate-600">
                                    Asset:
                                    ${escapeHtml(
                                        item.asset_number
                                        || '—',
                                    )}
                                </p>

                                <p class="text-xs text-slate-400">
                                    ${escapeHtml(
                                        item.barcode
                                        || 'No barcode',
                                    )}
                                </p>
                            </div>

                            <span class="shrink-0 rounded-full bg-blue-100 px-3 py-1 text-xs font-semibold text-blue-700">
                                Assigned
                            </span>
                        </div>
                    </div>
                `,
            )
            .join('');
}

function prepareAssignedUnits() {
    scannedUnits.clear();

    borrowing.items.forEach((item) => {
        const itemUnitId =
            Number(item.item_unit_id);

        scannedUnits.set(
            itemUnitId,
            {
                id: itemUnitId,

                borrowing_item_id:
                    Number(
                        item.borrowing_item_id,
                    ),

                barcode:
                    item.barcode,

                asset_number:
                    item.asset_number,

                item_name:
                    item.item_name,

                category:
                    item.category,

                condition:
                    item.condition,

                availability_status:
                    item.availability_status,
            },
        );
    });

    if (progress) {
        progress.textContent =
            `${scannedUnits.size} / ${borrowing.items.length}`;
    }
}

function confirmationEquipmentList() {
    return borrowing.items
        .map(
            (item) => `
                <li class="flex items-start justify-between gap-4 rounded-xl border border-slate-200 bg-slate-50 px-4 py-3">
                    <div class="min-w-0">
                        <p class="truncate font-semibold text-slate-900">
                            ${escapeHtml(
                                item.item_name,
                            )}
                        </p>

                        <p class="text-xs text-slate-500">
                            ${escapeHtml(
                                item.asset_number
                                || item.barcode
                                || 'No asset number',
                            )}
                        </p>
                    </div>

                    <span class="shrink-0 rounded-full bg-white px-3 py-1 text-xs font-semibold capitalize text-slate-600">
                        ${escapeHtml(
                            String(
                                item.condition
                                || 'unknown',
                            )
                                .replaceAll(
                                    '_',
                                    ' ',
                                ),
                        )}
                    </span>
                </li>
            `,
        )
        .join('');
}

function openConfirmationModal() {
    if (
        !borrowing
        || confirmationModal
    ) {
        return;
    }

    const isRelease =
        mode === 'release';

    const actionTitle = isRelease
        ? 'Confirm Equipment Release'
        : 'Confirm Equipment Return';

    const actionDescription = isRelease
        ? 'Confirm that the borrower is receiving all equipment listed below.'
        : 'Confirm that all equipment listed below has been returned. Existing equipment conditions will be preserved.';

    const buttonLabel = isRelease
        ? 'Confirm Release'
        : 'Confirm Return';

    const buttonClasses = isRelease
        ? 'bg-violet-600 hover:bg-violet-700'
        : 'bg-green-700 hover:bg-green-800';

    confirmationModal =
        document.createElement('div');

    confirmationModal.id =
        'scannerConfirmationModal';

    confirmationModal.className =
        'fixed inset-0 z-50 flex items-center justify-center bg-black/60 p-4';

    confirmationModal.innerHTML = `
        <div class="flex max-h-[92vh] w-full max-w-3xl flex-col overflow-hidden rounded-2xl bg-white shadow-2xl">
            <div class="flex items-start justify-between gap-4 border-b border-slate-200 px-6 py-5">
                <div>
                    <h2 class="text-xl font-bold text-slate-900">
                        ${actionTitle}
                    </h2>

                    <p class="mt-1 text-sm text-slate-500">
                        ${actionDescription}
                    </p>
                </div>

                <button
                    type="button"
                    id="closeConfirmationModal"
                    class="rounded-lg px-3 py-2 text-slate-500 transition hover:bg-slate-100 hover:text-slate-800"
                    aria-label="Close confirmation"
                >
                    ✕
                </button>
            </div>

            <div class="overflow-y-auto p-6">
                <div class="rounded-2xl border border-slate-200 bg-white p-5">
                    <div class="grid gap-4 sm:grid-cols-2">
                        <div>
                            <p class="text-xs font-semibold uppercase tracking-wide text-slate-400">
                                Selected Action
                            </p>

                            <p class="mt-1 font-bold text-slate-900">
                                ${isRelease
                                    ? 'Release Equipment'
                                    : 'Return Equipment'}
                            </p>
                        </div>

                        <div>
                            <p class="text-xs font-semibold uppercase tracking-wide text-slate-400">
                                Borrowing Code
                            </p>

                            <p class="mt-1 font-bold text-slate-900">
                                ${escapeHtml(
                                    borrowing.code,
                                )}
                            </p>
                        </div>

                        <div>
                            <p class="text-xs font-semibold uppercase tracking-wide text-slate-400">
                                Borrower
                            </p>

                            <p class="mt-1 font-semibold text-slate-900">
                                ${escapeHtml(
                                    borrowing.user?.name,
                                )}
                            </p>
                        </div>

                        <div>
                            <p class="text-xs font-semibold uppercase tracking-wide text-slate-400">
                                ID Number
                            </p>

                            <p class="mt-1 font-semibold text-slate-900">
                                ${escapeHtml(
                                    borrowing.user
                                        ?.id_number
                                    || '—',
                                )}
                            </p>
                        </div>

                        <div>
                            <p class="text-xs font-semibold uppercase tracking-wide text-slate-400">
                                Current Status
                            </p>

                            <p class="mt-1 font-semibold capitalize text-slate-900">
                                ${escapeHtml(
                                    borrowing.status,
                                )}
                            </p>
                        </div>

                        <div>
                            <p class="text-xs font-semibold uppercase tracking-wide text-slate-400">
                                Equipment Count
                            </p>

                            <p class="mt-1 font-semibold text-slate-900">
                                ${borrowing.items.length}
                            </p>
                        </div>

                        <div class="sm:col-span-2">
                            <p class="text-xs font-semibold uppercase tracking-wide text-slate-400">
                                Purpose
                            </p>

                            <p class="mt-1 text-sm text-slate-800">
                                ${escapeHtml(
                                    borrowing.purpose
                                    || '—',
                                )}
                            </p>
                        </div>
                    </div>
                </div>

                <div class="mt-5">
                    <h3 class="font-bold text-slate-900">
                        Assigned Equipment
                    </h3>

                    <ul class="mt-3 space-y-3">
                        ${confirmationEquipmentList()}
                    </ul>
                </div>

                <div class="mt-5 rounded-xl border border-amber-200 bg-amber-50 px-4 py-3 text-sm text-amber-800">
                    <p class="font-semibold">
                        Please verify before continuing.
                    </p>

                    <p class="mt-1">
                        This action will update the borrowing transaction and every assigned equipment unit.
                    </p>
                </div>
            </div>

            <div class="flex flex-col-reverse gap-3 border-t border-slate-200 px-6 py-5 sm:flex-row sm:justify-end">
                <button
                    type="button"
                    id="cancelConfirmation"
                    class="rounded-xl border border-slate-300 px-5 py-3 font-semibold text-slate-700 transition hover:bg-slate-50"
                >
                    Cancel
                </button>

                <button
                    type="button"
                    id="confirmScannerTransaction"
                    class="rounded-xl px-5 py-3 font-semibold text-white transition disabled:cursor-not-allowed disabled:opacity-60 ${buttonClasses}"
                >
                    ${buttonLabel}
                </button>
            </div>
        </div>
    `;

    document.body.appendChild(
        confirmationModal,
    );

    document.body.classList.add(
        'overflow-hidden',
    );

    confirmationModal
        .querySelector(
            '#closeConfirmationModal',
        )
        ?.addEventListener(
            'click',
            cancelPendingTransaction,
        );

    confirmationModal
        .querySelector(
            '#cancelConfirmation',
        )
        ?.addEventListener(
            'click',
            cancelPendingTransaction,
        );

    confirmationModal
        .querySelector(
            '#confirmScannerTransaction',
        )
        ?.addEventListener(
            'click',
            confirmTransaction,
        );

    confirmationModal.addEventListener(
        'click',
        (event) => {
            if (
                event.target
                === confirmationModal
            ) {
                cancelPendingTransaction();
            }
        },
    );
}

function cancelPendingTransaction() {
    closeConfirmationModal();

    resetSession();
}

function setConfirmationLoading(
    loading,
) {
    const button =
        confirmationModal?.querySelector(
            '#confirmScannerTransaction',
        );

    const cancelButton =
        confirmationModal?.querySelector(
            '#cancelConfirmation',
        );

    const closeButton =
        confirmationModal?.querySelector(
            '#closeConfirmationModal',
        );

    if (!button) {
        return;
    }

    button.disabled = loading;

    if (cancelButton) {
        cancelButton.disabled = loading;
    }

    if (closeButton) {
        closeButton.disabled = loading;
    }

    if (loading) {
        button.textContent =
            mode === 'release'
                ? 'Processing Release...'
                : 'Processing Return...';
    } else {
        button.textContent =
            mode === 'release'
                ? 'Confirm Release'
                : 'Confirm Return';
    }
}

async function confirmTransaction() {
    if (
        !borrowing
        || sessionCompleted
    ) {
        return;
    }

    setConfirmationLoading(true);

    if (mode === 'release') {
        await finishRelease();
    } else {
        await finishReturn();
    }
}

async function finishRelease() {
    try {
        const response = await axios.post(
            '/scanner/finish-release',
            {
                borrowing_id:
                    borrowing.id,

                items: Array.from(
                    scannedUnits.keys(),
                ),
            },
        );

        sessionCompleted = true;

        closeConfirmationModal();

        showMessage(
            response.data.message
            ?? 'Equipment released successfully.',
            'success',
        );

        updateBorrowingAfterCompletion(
            'released',
        );

        showCompletedButton();
    } catch (error) {
        showMessage(
            getErrorMessage(
                error,
                'Unable to complete the release.',
            ),
            'error',
        );

        setConfirmationLoading(false);
    }
}

async function finishReturn() {
    const allowedConditions = [
        'excellent',
        'good',
        'fair',
        'damaged',
        'for_repair',
        'unserviceable',
    ];

    const conditions = {};
    const remarks = {};

    borrowing.items.forEach((item) => {
        const borrowingItemId =
            Number(
                item.borrowing_item_id,
            );

        conditions[borrowingItemId] =
            allowedConditions.includes(
                item.condition,
            )
                ? item.condition
                : 'good';
    });

    try {
        const response = await axios.post(
            '/scanner/finish-return',
            {
                borrowing_id:
                    borrowing.id,

                items: Array.from(
                    scannedUnits.keys(),
                ),

                conditions,
                remarks,
            },
        );

        sessionCompleted = true;

        closeConfirmationModal();

        showMessage(
            response.data.message
            ?? 'Return processed successfully.',
            'success',
        );

        updateBorrowingAfterCompletion(
            'returned',
        );

        showCompletedButton();
    } catch (error) {
        showMessage(
            getErrorMessage(
                error,
                'Unable to complete the return.',
            ),
            'error',
        );

        setConfirmationLoading(false);
    }
}

function updateBorrowingAfterCompletion(
    newStatus,
) {
    if (!borrowing) {
        return;
    }

    borrowing.status = newStatus;

    renderBorrowing();
}

function showCompletedButton() {
    removeActionButton();

    if (!equipmentList) {
        return;
    }

    const button =
        document.createElement('button');

    button.id =
        'scannerActionButton';

    button.type = 'button';

    button.className =
        'mt-5 w-full rounded-xl bg-blue-600 px-4 py-3 font-semibold text-white transition hover:bg-blue-700';

    button.textContent =
        'Scan Another Borrowing';

    button.addEventListener(
        'click',
        resetSession,
    );

    equipmentList.insertAdjacentElement(
        'afterend',
        button,
    );
}

async function onScanSuccess(
    decodedText,
) {
    if (
        scanLocked
        || sessionCompleted
        || confirmationModal
    ) {
        return;
    }

    scanLocked = true;

    try {
        await loadBorrowing(
            decodedText,
        );
    } finally {
        window.setTimeout(() => {
            scanLocked = false;
        }, 1500);
    }
}

function onScanFailure() {
    // Camera decode failures are normal
    // while waiting for a valid QR code.
}

releaseModeButton?.addEventListener(
    'click',
    () => {
        mode = 'release';

        resetSession();
    },
);

returnModeButton?.addEventListener(
    'click',
    () => {
        mode = 'return';

        resetSession();
    },
);

document.addEventListener(
    'keydown',
    (event) => {
        if (
            event.key === 'Escape'
            && confirmationModal
        ) {
            cancelPendingTransaction();
        }
    },
);

if (
    typeof Html5QrcodeScanner
        !== 'undefined'
    && document.getElementById('reader')
) {
    const scanner =
        new Html5QrcodeScanner(
            'reader',
            {
                fps: 10,

                qrbox: {
                    width: 250,
                    height: 250,
                },

                rememberLastUsedCamera:
                    true,

                supportedScanTypes: [
                    Html5QrcodeScanType
                        .SCAN_TYPE_CAMERA,

                    Html5QrcodeScanType
                        .SCAN_TYPE_FILE,
                ],
            },
            false,
        );

    scanner.render(
        onScanSuccess,
        onScanFailure,
    );
} else {
    showMessage(
        'QR scanner library failed to load.',
        'error',
    );
}

resetSession();