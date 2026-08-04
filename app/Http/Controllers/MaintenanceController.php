<?php

namespace App\Http\Controllers;

use App\Models\ItemUnit;
use App\Models\MaintenanceRecord;
use App\Models\User;
use App\Support\CampusAccess;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

class MaintenanceController extends Controller
{
    public function index(Request $request): View
    {
        abort_unless($request->user()->can('view maintenance'), 403);
        $query = MaintenanceRecord::with(['itemUnit.item.category','assignee'])
            ->whereHas('itemUnit', fn ($unitQuery) => $unitQuery->visibleTo($request->user()))
            ->latest();
        if ($request->filled('status')) $query->where('status', $request->string('status'));
        if ($request->filled('priority')) $query->where('priority', $request->string('priority'));
        if ($request->filled('search')) {
            $search = $request->string('search');
            $query->where(function ($q) use ($search) {
                $q->where('maintenance_code', 'like', "%{$search}%")
                    ->orWhere('issue_title', 'like', "%{$search}%")
                    ->orWhereHas('itemUnit', fn ($u) => $u->where('asset_number', 'like', "%{$search}%")->orWhere('barcode_value', 'like', "%{$search}%"))
                    ->orWhereHas('itemUnit.item', fn ($i) => $i->where('name', 'like', "%{$search}%"));
            });
        }
        $records = $query->paginate(15)->withQueryString();
        $maintenanceQuery = fn () => MaintenanceRecord::query()
            ->whereHas('itemUnit', fn ($unitQuery) => $unitQuery->visibleTo($request->user()));

        $counts = [
            'open' => $maintenanceQuery()->whereIn('status', ['reported','assigned','in_progress'])->count(),
            'critical' => $maintenanceQuery()->whereIn('status', ['reported','assigned','in_progress'])->where('priority','critical')->count(),
            'completed' => $maintenanceQuery()->where('status','completed')->count(),
            'cost' => $maintenanceQuery()->where('status','completed')->sum('repair_cost'),
        ];
        return view('maintenance.index', compact('records','counts'));
    }

    public function create(Request $request): View
    {
        abort_unless($request->user()->can('create maintenance'), 403);
        $units = ItemUnit::query()->visibleTo($request->user())->with('item')->whereNotIn('availability_status', ['borrowed','reserved','archived'])->orderBy('asset_number')->get();
        return view('maintenance.create', compact('units'));
    }

    public function store(Request $request): RedirectResponse
    {
        abort_unless($request->user()->can('create maintenance'), 403);
        $data = $request->validate([
            'item_unit_id' => ['required','exists:item_units,id'],
            'priority' => ['required','in:low,medium,high,critical'],
            'issue_title' => ['required','string','max:255'],
            'issue_description' => ['nullable','string'],
        ]);
        $record = DB::transaction(function () use ($data, $request) {
            $unit = ItemUnit::lockForUpdate()->findOrFail($data['item_unit_id']);
            CampusAccess::ensureCanAccess($request->user(), $unit->campus);
            abort_if(in_array($unit->availability_status, ['borrowed','reserved','archived'], true), 422, 'This unit cannot be placed under maintenance.');
            $record = MaintenanceRecord::create($data + [
                'maintenance_code' => $this->nextCode(),
                'reported_by' => $request->user()->id,
                'condition_before' => $unit->condition,
            ]);
            $unit->update(['availability_status'=>'maintenance','updated_by'=>$request->user()->id]);
            $unit->item->refreshQuantities();
            return $record;
        });
        return redirect()->route('maintenance.show', $record)->with('success','Maintenance record created.');
    }

    public function show(Request $request, MaintenanceRecord $maintenance): View
    {
        abort_unless($request->user()->can('view maintenance'), 403);
        CampusAccess::ensureCanAccess($request->user(), $maintenance->itemUnit->campus);
        $maintenance->load(['itemUnit.item.category','borrowing','reporter','assignee','completer']);
        $technicians = User::role(['admin','super_admin'])
            ->when(! CampusAccess::canViewAllCampuses($request->user()), fn ($query) => $query->where('campus', CampusAccess::userCampus($request->user())))
            ->orderBy('first_name')
            ->get();
        return view('maintenance.show', compact('maintenance','technicians'));
    }

    public function assign(Request $request, MaintenanceRecord $maintenance): RedirectResponse
    {
        abort_unless($request->user()->can('manage maintenance'), 403);
        CampusAccess::ensureCanAccess($request->user(), $maintenance->itemUnit->campus);
        $data = $request->validate(['assigned_to'=>['required','exists:users,id']]);
        abort_unless(in_array($maintenance->status, ['reported','assigned'], true), 422);

        $technician = User::query()->findOrFail($data['assigned_to']);
        abort_unless(
            hash_equals((string) $maintenance->itemUnit->campus, (string) $technician->campus),
            422,
            'The assigned technician must belong to the same campus as the equipment.'
        );

        $maintenance->update(['assigned_to'=>$technician->id,'status'=>'assigned']);
        return back()->with('success','Technician assigned.');
    }

    public function start(Request $request, MaintenanceRecord $maintenance): RedirectResponse
    {
        abort_unless($request->user()->can('manage maintenance'), 403);
        CampusAccess::ensureCanAccess($request->user(), $maintenance->itemUnit->campus);
        abort_unless(in_array($maintenance->status, ['reported','assigned'], true), 422);
        $data = $request->validate(['diagnosis'=>['nullable','string']]);
        $maintenance->update(['status'=>'in_progress','assigned_to'=>$maintenance->assigned_to ?: $request->user()->id,'started_at'=>now(),'diagnosis'=>$data['diagnosis'] ?? $maintenance->diagnosis]);
        return back()->with('success','Repair work started.');
    }

    public function complete(Request $request, MaintenanceRecord $maintenance): RedirectResponse
    {
        abort_unless($request->user()->can('manage maintenance'), 403);
        CampusAccess::ensureCanAccess($request->user(), $maintenance->itemUnit->campus);
        abort_unless(in_array($maintenance->status, ['reported','assigned','in_progress'], true), 422);
        $data = $request->validate([
            'condition_after'=>['required','in:excellent,good,fair,damaged,for_repair,unserviceable'],
            'repair_action'=>['required','string'],
            'repair_cost'=>['nullable','numeric','min:0'],
            'completion_notes'=>['nullable','string'],
        ]);
        DB::transaction(function () use ($maintenance,$request,$data) {
            $available = in_array($data['condition_after'], ['excellent','good','fair'], true);
            $maintenance->update($data + ['status'=>'completed','completed_by'=>$request->user()->id,'completed_at'=>now()]);
            $maintenance->itemUnit->update([
                'condition'=>$data['condition_after'],
                'availability_status'=>$available ? 'available' : 'maintenance',
                'updated_by'=>$request->user()->id,
            ]);
            $maintenance->itemUnit->item->refreshQuantities();
        });
        return back()->with('success','Maintenance completed and unit status updated.');
    }

    public function cancel(Request $request, MaintenanceRecord $maintenance): RedirectResponse
    {
        abort_unless($request->user()->can('manage maintenance'), 403);
        CampusAccess::ensureCanAccess($request->user(), $maintenance->itemUnit->campus);
        abort_unless(in_array($maintenance->status, ['reported','assigned'], true), 422);
        DB::transaction(function () use ($maintenance,$request) {
            $condition = $maintenance->itemUnit->condition;
            $maintenance->update(['status'=>'cancelled','completed_by'=>$request->user()->id,'completed_at'=>now()]);
            $maintenance->itemUnit->update(['availability_status'=>in_array($condition,['excellent','good','fair'],true)?'available':'maintenance','updated_by'=>$request->user()->id]);
            $maintenance->itemUnit->item->refreshQuantities();
        });
        return back()->with('success','Maintenance record cancelled.');
    }

    private function nextCode(): string
    {
        $prefix = 'MNT-' . now()->format('Ym') . '-';
        $last = MaintenanceRecord::where('maintenance_code','like',$prefix.'%')->lockForUpdate()->orderByDesc('id')->value('maintenance_code');
        return $prefix . str_pad((string) ($last ? ((int) substr($last,-5))+1 : 1), 5, '0', STR_PAD_LEFT);
    }
}
