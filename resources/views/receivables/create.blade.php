<x-app-layout>

<x-slot name="header">
<h2 class="font-semibold text-2xl text-gray-800 leading-tight">
Tambah Piutang Manual
</h2>
</x-slot>

<div class="py-6">
<div class="max-w-4xl mx-auto sm:px-6 lg:px-8">

<div class="bg-white shadow rounded p-6">

<form method="POST" action="{{ route('receivables.store') }}">
@csrf

<div class="mb-4">
<label class="block text-sm font-medium">Toko</label>

<input
type="text"
id="store_search"
class="border rounded w-full px-3 py-2"
placeholder="ketik nama toko">

<input type="hidden" name="store_id" id="store_id">

<div id="store_result" class="border mt-1 bg-white"></div>

</div>

<div class="mb-4">
<label>Nominal Piutang</label>
<input type="number"
name="amount"
required
class="border rounded w-full px-3 py-2">
</div>

<div class="mb-4">
<label>Jatuh Tempo</label>
<input type="date"
name="due_date"
class="border rounded w-full px-3 py-2">
</div>

<button
class="bg-blue-600 text-white px-4 py-2 rounded">
Simpan Piutang
</button>

</form>

</div>
</div>
</div>

<script>

const input = document.getElementById('store_search')
const result = document.getElementById('store_result')
const hidden = document.getElementById('store_id')

input.addEventListener('keyup',function(){

fetch('/stores/search?q='+this.value)
.then(r=>r.json())
.then(data=>{

result.innerHTML=''

data.forEach(store=>{

let div=document.createElement('div')
div.innerText=store.name
div.className="p-2 hover:bg-gray-100 cursor-pointer"

div.onclick=function(){

input.value=store.name
hidden.value=store.id
result.innerHTML=''

}

result.appendChild(div)

})

})

})

</script>

</x-app-layout>