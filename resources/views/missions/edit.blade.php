<x-app-layout>

<x-slot name="header">
    <h2 class="font-semibold text-lg text-gray-800 leading-tight">
        Edit Misi Sales
    </h2>
</x-slot>

<div class="py-6">
<div class="max-w-xl mx-auto sm:px-6 lg:px-8">

<div class="bg-white shadow sm:rounded-lg p-6">

<form method="POST" action="{{ route('missions.update',$mission->id) }}">

@csrf
@method('PUT')

<div class="mb-4">
<label class="block mb-1">Judul</label>
<input type="text"
name="title"
value="{{ $mission->title }}"
class="w-full border rounded p-2">
</div>

<div class="mb-4">
<label class="block mb-1">Jenis</label>
<input type="text"
name="type"
value="{{ $mission->type }}"
class="w-full border rounded p-2">
</div>

<div class="mb-4">
<label class="block mb-1">Target</label>
<input type="number"
name="target"
value="{{ $mission->target }}"
class="w-full border rounded p-2">
</div>

<div class="mb-4">
<label class="block mb-1">Reward</label>
<input type="number"
name="reward_amount"
value="{{ $mission->reward_amount }}"
class="w-full border rounded p-2">
</div>

<div class="mb-4">
<label class="block mb-1">Start Date</label>
<input type="date"
name="start_date"
value="{{ $mission->start_date }}"
class="w-full border rounded p-2">
</div>

<div class="mb-4">
<label class="block mb-1">End Date</label>
<input type="date"
name="end_date"
value="{{ $mission->end_date }}"
class="w-full border rounded p-2">
</div>

<div class="mb-4">
<label class="flex items-center gap-2">
<input type="checkbox" name="active" {{ $mission->active ? 'checked' : '' }}>
Aktif
</label>
</div>

<button class="bg-blue-600 text-white px-4 py-2 rounded">
Update Misi
</button>

<a href="{{ route('missions.index') }}"
class="ml-2 text-gray-600">
Batal
</a>

</form>

</div>

</div>
</div>

</x-app-layout>