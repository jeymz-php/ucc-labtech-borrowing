<x-app-layout>
    <x-slot name="header">
        <div class="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
            <div><h1 class="text-xl font-bold text-gray-900">Reservation Calendar</h1><p class="mt-1 text-sm text-gray-500">Review schedules, check equipment availability, and prevent booking conflicts.</p></div>
            @can('create borrowing requests')<a href="{{ route('borrowings.create') }}" class="rounded-xl bg-green-700 px-4 py-2.5 text-sm font-semibold text-white hover:bg-green-800">New Borrowing Request</a>@endcan
        </div>
    </x-slot>

    <div x-data="reservationCalendar()" x-init="init()" class="space-y-6">
        @if(session('success'))<div class="rounded-xl border border-green-200 bg-green-50 px-4 py-3 text-sm text-green-800">{{ session('success') }}</div>@endif
        @if($errors->any())<div class="rounded-xl border border-red-200 bg-red-50 px-4 py-3 text-sm text-red-700">{{ $errors->first() }}</div>@endif

        <div class="grid gap-4 rounded-2xl border border-gray-200 bg-white p-5 shadow-sm md:grid-cols-4">
            <select x-model="filters.status" @change="loadEvents()" class="rounded-xl border-gray-300 text-sm"><option value="">All statuses</option>@foreach(['pending','approved','released','overdue','returned','rejected','cancelled'] as $status)<option value="{{ $status }}">{{ ucfirst($status) }}</option>@endforeach</select>
            <select x-model="filters.category_id" @change="filterItems(); loadEvents()" class="rounded-xl border-gray-300 text-sm"><option value="">All categories</option>@foreach($categories as $category)<option value="{{ $category->id }}">{{ $category->name }}</option>@endforeach</select>
            <select x-model="filters.item_id" @change="loadEvents()" class="rounded-xl border-gray-300 text-sm"><option value="">All items</option><template x-for="item in visibleItems" :key="item.id"><option :value="item.id" x-text="item.name"></option></template></select>
            <button @click="downloadPdf()" class="rounded-xl border border-green-700 px-4 py-2 text-sm font-semibold text-green-700 hover:bg-green-50">Export Calendar PDF</button>
        </div>

        <div class="grid gap-6 xl:grid-cols-[1fr_340px]">
            <section class="rounded-2xl border border-gray-200 bg-white p-5 shadow-sm">
                <div class="mb-5 flex items-center justify-between"><button @click="previousMonth()" class="rounded-lg border px-3 py-2">←</button><h2 class="text-lg font-bold" x-text="monthTitle"></h2><div class="flex gap-2"><button @click="today()" class="rounded-lg border px-3 py-2 text-sm">Today</button><button @click="nextMonth()" class="rounded-lg border px-3 py-2">→</button></div></div>
                <div class="grid grid-cols-7 border-l border-t text-center text-xs font-semibold uppercase text-gray-500"><template x-for="day in ['Sun','Mon','Tue','Wed','Thu','Fri','Sat']"><div class="border-b border-r bg-gray-50 p-2" x-text="day"></div></template></div>
                <div class="grid grid-cols-7 border-l"><template x-for="cell in calendarCells" :key="cell.key"><button type="button" @click="selectDate(cell.date)" class="min-h-28 border-b border-r p-2 text-left align-top hover:bg-green-50" :class="{'bg-gray-50 text-gray-400': !cell.currentMonth, 'ring-2 ring-inset ring-green-600': selectedDate === cell.iso}"><div class="text-sm font-semibold" x-text="cell.day"></div><div class="mt-1 space-y-1"><template x-for="event in eventsForDate(cell.iso).slice(0,3)" :key="event.id"><div class="truncate rounded px-1.5 py-1 text-[11px] font-medium" :class="statusClass(event.status)" x-text="event.code"></div></template><div x-show="eventsForDate(cell.iso).length > 3" class="text-[11px] text-gray-500" x-text="'+'+(eventsForDate(cell.iso).length-3)+' more'"></div></div></button></template></div>
            </section>

            <aside class="space-y-6">
                <div class="rounded-2xl border border-gray-200 bg-white p-5 shadow-sm"><h3 class="font-bold text-gray-900">Selected Date</h3><p class="mt-1 text-sm text-gray-500" x-text="selectedDateLabel"></p><div class="mt-4 max-h-96 space-y-3 overflow-auto"><template x-for="event in selectedEvents" :key="event.id"><a :href="event.url" class="block rounded-xl border p-3 hover:border-green-400"><div class="flex items-center justify-between gap-2"><strong class="text-sm" x-text="event.code"></strong><span class="rounded-full px-2 py-0.5 text-xs" :class="statusClass(event.status)" x-text="event.status"></span></div><div class="mt-1 text-sm text-gray-700" x-text="event.borrower"></div><div class="mt-1 text-xs text-gray-500" x-text="formatRange(event.start,event.end)"></div></a></template><p x-show="selectedEvents.length===0" class="text-sm text-gray-500">No reservations on this date.</p></div></div>

                <div class="rounded-2xl border border-gray-200 bg-white p-5 shadow-sm"><h3 class="font-bold text-gray-900">Availability Checker</h3><div class="mt-4 space-y-3"><input x-model="availability.borrow_at" type="datetime-local" class="w-full rounded-xl border-gray-300 text-sm"><input x-model="availability.expected_return_at" type="datetime-local" class="w-full rounded-xl border-gray-300 text-sm"><select x-model="availability.item_id" class="w-full rounded-xl border-gray-300 text-sm"><option value="">All equipment</option>@foreach($items as $item)<option value="{{ $item->id }}">{{ $item->name }}</option>@endforeach</select><button @click="checkAvailability()" class="w-full rounded-xl bg-green-700 px-4 py-2 text-sm font-semibold text-white">Check Availability</button></div><div x-show="availabilityResult" class="mt-4"><p class="text-sm font-semibold"><span x-text="availabilityResult?.count || 0"></span> unit(s) available</p><div class="mt-2 max-h-48 space-y-2 overflow-auto"><template x-for="unit in availabilityResult?.units || []" :key="unit.id"><div class="rounded-lg bg-gray-50 p-2 text-xs"><strong x-text="unit.asset_number"></strong> · <span x-text="unit.item"></span></div></template></div></div></div>
            </aside>
        </div>
    </div>

    <script>
    function reservationCalendar(){return{
        current:new Date(), selectedDate:new Date().toISOString().slice(0,10), events:[], items:@json($items), visibleItems:@json($items), filters:{status:'',category_id:'',item_id:''}, availability:{borrow_at:'',expected_return_at:'',item_id:''}, availabilityResult:null,
        init(){this.loadEvents()}, get monthTitle(){return this.current.toLocaleDateString(undefined,{month:'long',year:'numeric'})}, get selectedDateLabel(){return new Date(this.selectedDate+'T00:00:00').toLocaleDateString(undefined,{weekday:'long',month:'long',day:'numeric',year:'numeric'})},
        get calendarCells(){let y=this.current.getFullYear(),m=this.current.getMonth(),first=new Date(y,m,1),start=new Date(y,m,1-first.getDay()),cells=[];for(let i=0;i<42;i++){let d=new Date(start);d.setDate(start.getDate()+i);cells.push({key:d.toISOString(),date:d,iso:this.localIso(d),day:d.getDate(),currentMonth:d.getMonth()===m})}return cells},
        get selectedEvents(){return this.eventsForDate(this.selectedDate)}, localIso(d){let z=new Date(d.getTime()-d.getTimezoneOffset()*60000);return z.toISOString().slice(0,10)}, selectDate(d){this.selectedDate=this.localIso(d)}, previousMonth(){this.current=new Date(this.current.getFullYear(),this.current.getMonth()-1,1);this.loadEvents()}, nextMonth(){this.current=new Date(this.current.getFullYear(),this.current.getMonth()+1,1);this.loadEvents()}, today(){this.current=new Date();this.selectedDate=this.localIso(new Date());this.loadEvents()},
        async loadEvents(){let y=this.current.getFullYear(),m=this.current.getMonth(),start=new Date(y,m-1,1),end=new Date(y,m+2,1),p=new URLSearchParams({start:start.toISOString(),end:end.toISOString(),...this.filters});for(let[k,v]of[...p])if(!v)p.delete(k);let r=await fetch('{{ route('calendar.events') }}?'+p,{headers:{Accept:'application/json'}});this.events=await r.json()},
        eventsForDate(iso){let s=new Date(iso+'T00:00:00'),e=new Date(iso+'T23:59:59');return this.events.filter(v=>new Date(v.start)<=e&&new Date(v.end)>=s)}, filterItems(){this.visibleItems=this.filters.category_id?this.items.filter(i=>String(i.category_id)===String(this.filters.category_id)):this.items;this.filters.item_id=''},
        statusClass(s){return {'pending':'bg-amber-100 text-amber-800','approved':'bg-blue-100 text-blue-800','released':'bg-purple-100 text-purple-800','overdue':'bg-red-100 text-red-800','returned':'bg-green-100 text-green-800','rejected':'bg-gray-200 text-gray-700','cancelled':'bg-gray-100 text-gray-500'}[s]||'bg-gray-100'}, formatRange(a,b){return new Date(a).toLocaleString()+' – '+new Date(b).toLocaleString()},
        async checkAvailability(){if(!this.availability.borrow_at||!this.availability.expected_return_at)return;let p=new URLSearchParams(this.availability);for(let[k,v]of[...p])if(!v)p.delete(k);let r=await fetch('{{ route('calendar.availability') }}?'+p,{headers:{Accept:'application/json'}});this.availabilityResult=r.ok?await r.json():null},
        downloadPdf(){let y=this.current.getFullYear(),m=this.current.getMonth(),start=new Date(y,m,1),end=new Date(y,m+1,1),p=new URLSearchParams({start:start.toISOString(),end:end.toISOString(),status:this.filters.status});if(!this.filters.status)p.delete('status');window.location='{{ route('calendar.pdf') }}?'+p}
    }}
    </script>
</x-app-layout>
