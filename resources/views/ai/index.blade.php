<x-app-layout>

<x-slot name="header">
<h2 class="font-semibold text-xl text-gray-800 leading-tight">
🤖 AI Dhardhes
</h2>
</x-slot>

<div class="py-6">
<div class="max-w-6xl mx-auto sm:px-6 lg:px-8">

<div class="grid grid-cols-2 md:grid-cols-3 gap-6">

<a href="{{ route('ai.business') }}" class="bg-white p-6 rounded-xl shadow hover:shadow-lg transition">
<h3 class="font-semibold text-lg mb-2">Analisa Bisnis</h3>
<p class="text-sm text-gray-600">
Analisa performa distribusi berdasarkan data sistem.
</p>
</a>

</div>

</div>
</div>

</x-app-layout>