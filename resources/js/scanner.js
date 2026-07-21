import './bootstrap';
import axios from 'axios';

let mode = "release";
let borrowing = null;
let scannedItems = [];
let scanLocked = false;

const borrowingPanel = document.getElementById("borrowingPanel");
const equipmentList = document.getElementById("equipmentList");
const progress = document.getElementById("progress");
const status = document.getElementById("scannerStatus");

document.getElementById("releaseMode").addEventListener("click", () => {
    mode = "release";
    resetSession();
});

document.getElementById("returnMode").addEventListener("click", () => {
    mode = "return";
    resetSession();
});

function resetSession() {
    borrowing = null;
    scannedItems = [];

    borrowingPanel.innerHTML =
        "<p class='text-gray-400'>No borrowing loaded.</p>";

    equipmentList.innerHTML = "";

    progress.innerHTML = "0 / 0";

    status.innerHTML =
        mode === "release"
            ? "Waiting for Borrowing QR..."
            : "Waiting for Borrowing QR...";
}

async function loadBorrowing(code) {
    try {

        const response = await axios.post("/scanner/borrowing", {
            code: code
        });

        borrowing = response.data.borrowing;

        renderBorrowing();

        renderItems();

        showMessage(
            "Borrowing loaded. Scan equipment.",
            "success"
        );

    } catch (e) {

        showMessage(
            e.response?.data?.message ??
            "Borrowing not found.",
            "error"
        );

    }
}

async function scanEquipment(barcode) {

    if (!borrowing)
        return;

    if (
        scannedItems.includes(barcode)
    ) {
        showMessage(
            "Equipment already scanned.",
            "warning"
        );
        return;
    }

    try {

        const response = await axios.post(
            "/scanner/unit",
            {
                borrowing_id: borrowing.id,
                barcode: barcode,
                mode: mode
            }
        );

        scannedItems.push(barcode);

        markItem(barcode);

        updateProgress();

        status.innerHTML = response.data.message;

    } catch (e) {

        alert(
            e.response?.data?.message ??
            "Invalid equipment."
        );

    }

}

function renderBorrowing() {

    borrowingPanel.innerHTML = `

        <div class="space-y-2">

            <div>

                <span class="font-semibold">
                    Code:
                </span>

                ${borrowing.code}

            </div>

            <div>

                <span class="font-semibold">
                    Borrower:
                </span>

                ${borrowing.user.name}

            </div>

            <div>

                <span class="font-semibold">
                    Status:
                </span>

                ${borrowing.status}

            </div>

            <div>

                <span class="font-semibold">
                    Purpose:
                </span>

                ${borrowing.purpose}

            </div>

        </div>

    `;

}

function renderItems() {

    equipmentList.innerHTML = "";

    borrowing.items.forEach(item => {

        equipmentList.innerHTML += `

            <div
                id="item-${item.barcode}"
                class="border rounded-lg p-3">

                <div class="font-semibold">
                    ${item.item_name}
                </div>

                <div class="text-sm text-gray-500">
                    ${item.asset_number}
                </div>

                <div class="text-xs text-gray-400">
                    ${item.barcode}
                </div>

            </div>

        `;

    });

    updateProgress();

}

function markItem(barcode) {

    const row = document.getElementById(
        `item-${barcode}`
    );

    if (!row)
        return;

    row.classList.remove(
        "border"
    );

    row.classList.add(
        "bg-green-100",
        "border-green-500"
    );

}

function updateProgress() {

    progress.innerHTML =
        `${scannedItems.length} / ${borrowing.items.length}`;

    if (
        scannedItems.length ===
        borrowing.items.length
    ) {

        showMessage(
            "All equipment verified.",
            "success"
        );

        showFinishButton();
    }

}

function showFinishButton() {

    if (document.getElementById("finishScanner"))
        return;

    const btn = document.createElement("button");

    btn.id = "finishScanner";

    btn.className =
        "mt-5 w-full bg-green-600 hover:bg-green-700 text-white rounded-lg py-3 font-semibold";

    btn.innerHTML =
        mode === "release"
            ? "Finish Release"
            : "Finish Return";

    btn.onclick = finishScanning;

    equipmentList.parentElement.appendChild(btn);

}

function finishScanning() {

    console.log(
        scannedItems
    );

}

async function onScanSuccess(decodedText) {

    if (scanLocked) {
        return;
    }

    scanLocked = true;

    try {

        if (!borrowing) {
            await loadBorrowing(decodedText);
        } else {
            await scanEquipment(decodedText);
        }

    } finally {

        setTimeout(() => {
            scanLocked = false;
        }, 1200);

    }

}

const scanner = new Html5QrcodeScanner(
    "reader",
    {
        fps: 10,
        qrbox: 250
    },
    false
);

function showMessage(message, type = "success") {

    status.textContent = message;

    status.className =
        "mt-4 text-center font-semibold";

    switch (type) {

        case "success":
            status.classList.add("text-green-600");
            break;

        case "error":
            status.classList.add("text-red-600");
            break;

        case "warning":
            status.classList.add("text-yellow-600");
            break;

        default:
            status.classList.add("text-gray-600");
            break;

    }

}

scanner.render(onScanSuccess);

resetSession();