<?php

namespace App\Http\Controllers;

use App\Models\AuditLog;
use App\Models\User;
use App\Support\CampusAccess;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Request;
use Illuminate\View\View;
use Symfony\Component\HttpFoundation\StreamedResponse;

class AuditLogController extends Controller
{
    public function index(Request $request): View
    {
        abort_unless($request->user()->can('view activity logs'), 403);

        $logs = $this->query($request)->latest()->paginate(25)->withQueryString();
        $modulesQuery = AuditLog::query()->whereNotNull('module');
        $usersQuery = User::query();

        if (! CampusAccess::canViewAllCampuses($request->user())) {
            $campus = CampusAccess::userCampus($request->user());
            $modulesQuery->whereHas('user', fn ($query) => $query->where('campus', $campus));
            $usersQuery->where('campus', $campus);
        }

        $modules = $modulesQuery->distinct()->orderBy('module')->pluck('module');
        $users = $usersQuery->orderBy('first_name')->orderBy('last_name')->get();

        return view('audit-logs.index', compact('logs', 'modules', 'users'));
    }

    public function exportCsv(Request $request): StreamedResponse
    {
        abort_unless($request->user()->can('export activity logs'), 403);
        $rows = $this->query($request)->oldest()->get();

        return response()->streamDownload(function () use ($rows) {
            $out = fopen('php://output', 'w');
            fputcsv($out, ['Date', 'Campus', 'User', 'Role', 'Module', 'Action', 'Description', 'IP Address']);
            foreach ($rows as $log) {
                fputcsv($out, [
                    $log->created_at?->format('Y-m-d H:i:s'),
                    $log->user?->campus,
                    $log->user?->full_name ?? 'System',
                    $log->user?->getRoleNames()->join(', '),
                    $log->module,
                    $log->action,
                    $log->description,
                    $log->ip_address,
                ]);
            }
            fclose($out);
        }, 'audit-logs-'.now()->format('Ymd-His').'.csv', ['Content-Type' => 'text/csv']);
    }

    public function exportPdf(Request $request)
    {
        abort_unless($request->user()->can('export activity logs'), 403);
        $logs = $this->query($request)->oldest()->get();

        return Pdf::loadView('audit-logs.pdf', [
            'logs' => $logs,
            'preparedBy' => $request->user(),
            'generatedAt' => now(),
        ])->setPaper('a4', 'landscape')->download('audit-logs-'.now()->format('Ymd-His').'.pdf');
    }

    private function query(Request $request)
    {
        $query = AuditLog::query()->with('user.roles');

        if (! CampusAccess::canViewAllCampuses($request->user())) {
            $query->whereHas('user', fn ($userQuery) => $userQuery->where(
                'campus',
                CampusAccess::userCampus($request->user())
            ));
        }

        return $query
            ->when($request->filled('search'), function ($query) use ($request) {
                $search = $request->string('search')->toString();
                $query->where(function ($sub) use ($search) {
                    $sub->where('description', 'like', "%{$search}%")
                        ->orWhere('action', 'like', "%{$search}%")
                        ->orWhere('module', 'like', "%{$search}%")
                        ->orWhereHas('user', fn ($user) => $user
                            ->where('first_name', 'like', "%{$search}%")
                            ->orWhere('last_name', 'like', "%{$search}%"));
                });
            })
            ->when($request->filled('module'), fn ($query) => $query->where('module', $request->module))
            ->when($request->filled('user_id'), fn ($query) => $query->where('user_id', $request->integer('user_id')))
            ->when($request->filled('from'), fn ($query) => $query->whereDate('created_at', '>=', $request->date('from')))
            ->when($request->filled('to'), fn ($query) => $query->whereDate('created_at', '<=', $request->date('to')));
    }
}
