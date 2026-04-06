window.loadVariants = function(productId){

    const container = document.getElementById('variant-container');

    console.log('trigger jalan:', productId);

    if(!productId){
        container.innerHTML = '<div class="text-gray-500 text-sm">Pilih produk terlebih dahulu</div>';
        return;
    }

    window.toggleQty = function(checkbox, index) {
    const qtyInput = document.getElementById('qty-' + index);

    if (checkbox.checked) {
        qtyInput.disabled = false;
        qtyInput.focus();
    } else {
        qtyInput.disabled = true;
        qtyInput.value = '';
    }
}

    fetch('/api/product-variants/' + productId)
        .then(res => res.json())
        .then(data => {

            console.log('data:', data);

            if(data.length === 0){
                container.innerHTML = '<div class="text-red-500 text-sm">Belum ada varian</div>';
                return;
            }

            let html = '';

            data.forEach((variant, index) => {
                html += `
<div style="display:flex;align-items:center;gap:10px;margin-bottom:8px;">
    
    <input type="checkbox"
        onchange="toggleQty(this, ${index})">

    <input type="hidden"
        name="variants[${index}][id]"
        value="${variant.id}">

    <input type="text"
        value="${variant.name}"
        readonly
        style="flex:2;border:1px solid #ccc;padding:6px;border-radius:6px;">

    <input type="number"
        name="variants[${index}][qty]"
        id="qty-${index}"
        disabled
        min="1"
        placeholder="Jumlah"
        style="flex:1;border:1px solid #ccc;padding:6px;border-radius:6px;">
</div>
`;
            });

            container.innerHTML = html;

        });
}