class PanierElement
{
    static createProductRow(row,actionCallBack,changeCallBack)
    {
        let child = document.createElement("div")
        child.className = "row";
        child.setAttribute('data-product', row['ref']);
        child.innerHTML = "<div class='close' data-product='"+row['ref']+"' data-product-action='clearProduct'>"+
            "<i class='fas fa-times'></i>"+
        "</div>"+
        "<div class='image'>"+
            "<img src='"+row['photo']+"' alt='"+row['name']+"'>"+
        "</div>"+
        "<div class='info'>"+
            "<div class='title'>"+row['name']+"</div>"+
            "<div class='more'>"+
                "<div class='quantity'>Quantité : <span data-product='"+row['ref']+"' class='quantity_aff'>"+row['quantity']+"</span></div>"+
                "<div class='price'>"+row['price']+"€</div>"+
            "</div>"+
            "<div class='action'>"+
                "<div class='less' data-product='"+row['ref']+"' data-product-action='decrease'>"+
                    "<i class='fas fa-minus'></i>"+
                "</div>"+
                "<input data-product='"+row['ref']+"' class='quantity' type='number' value='"+row['quantity']+"'>"+
                "<div class='plus' data-product='"+row['ref']+"' data-product-action='add'>"+
                    "<i class='fas fa-plus'></i>"+
                "</div>"+
            "</div>"+
        "</div>";

        let actions = child.querySelectorAll("div[data-product][data-product-action]");

        actions.forEach(element => {
            element.addEventListener('click',PanierCallBack.productAct);
        })

        child.querySelector("input.quantity[data-product]").addEventListener("change",PanierCallBack.productChange);

        return child;
    }

    static emptyElement()
    {
        let empty = document.createElement('div');
        empty.className = "empty";
        empty.innerText = "Votre panier est actuellement vide";

        return empty;
    }

    static listElement()
    {
        let list = document.createElement('div');
        list.className = "list";

        return list;
    }
}
