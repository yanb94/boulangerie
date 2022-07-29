let panier = document.getElementById('panier');
let viewPanier = document.getElementById('viewPanier');
let panierView = document.querySelector('.panier');
let hidePage = document.querySelector('.hide-panier');
let closePanier = document.getElementById("close-panier");

// Manage open and close of cart modal
panier.addEventListener("click",PanierCallBack.toggleElement)
hidePage.addEventListener('click',PanierCallBack.toggleElement)
viewPanier.addEventListener('click',PanierCallBack.toggleElement)
closePanier.addEventListener('click',PanierCallBack.toggleElement)

panierView.addEventListener('click',function(e){
    e.stopPropagation();
})

// Perform corresponding action when an element is clicked

let actionButtons = document.querySelectorAll("div[data-product][data-product-action]");

actionButtons.forEach(element => {
    element.addEventListener("click",PanierCallBack.productAct);
});


// Perform corresponding action when value change
let inputProductsQuantity = document.querySelectorAll("input.quantity[data-product]");

inputProductsQuantity.forEach(element => {
    element.addEventListener('change',PanierCallBack.productChange);
});


// Clear cart
let clearButton = document.querySelector(".panier .cont-buttons .clear");
clearButton.addEventListener('click',PanierCallBack.panierClear);

function loadPanier()
{
    let viewRow = document.querySelectorAll(".row[data-product]");

    PanierAjax.load().then(
        (panier) => {
            let arrayRow = []
            let viewRowActual = [];
            
            if(panier['list'] == null)
            {
                document.querySelector('#panier .nb').innerHTML = 0;
                document.querySelector('#viewPanier .panier .tax .price-ht .nb').innerHTML = "0.00€";
                document.querySelector('#viewPanier .panier .tax .vat .nb').innerHTML = "0.00€";
                document.querySelector('#viewPanier .panier .tax .final-price .nb').innerHTML = "0.00€";

                if(document.querySelector(".panier .cont .empty") == null)
                {
                    document.querySelector(".panier .cont .list").remove();
                    document.querySelector(".panier .cont").appendChild(PanierElement.emptyElement());
                }
            }
            else
            {
                panier['list'].forEach(element => {
                    arrayRow.push(element['ref']);
                });

                if(document.querySelector(".panier .cont .list") == null)
                {
                    document.querySelector(".panier .cont .empty").remove();
                    document.querySelector(".panier .cont").appendChild(PanierElement.listElement());
                }

                viewRow.forEach(element => {
                    if(!arrayRow.includes(element.getAttribute('data-product')))
                    {
                        element.remove();
                    }
                    else
                    {
                        viewRowActual.push(element.getAttribute('data-product'));
                    }
                });
    
                arrayRow.forEach(element => {
                    if(!viewRowActual.includes(element))
                    {
                        let row = {};
                        panier['list'].forEach(t => {
                            if(t['ref'] == element)
                            {
                                row = t;
                            } 
                        })
                        
                        document.querySelector(".panier .cont .list").appendChild(
                            PanierElement.createProductRow(
                                row,
                                PanierCallBack.productAct,
                                PanierCallBack.productChange
                            )
                        );
                    }
                })
    
                panier['list'].forEach(element => {
                    document.querySelector("input.quantity[data-product='"+element['ref']+"']").value = element['quantity'];
                    document.querySelector("span.quantity_aff[data-product='"+element['ref']+"']").innerHTML = element['quantity'];
                });

                document.querySelector('#panier .nb').innerHTML = panier['nb'];
                document.querySelector('#viewPanier .panier .tax .price-ht .nb').innerHTML = panier['price_ht'].toFixed(2)+"€";
                document.querySelector('#viewPanier .panier .tax .vat .nb').innerHTML = panier['vat'].toFixed(2)+"€";
                document.querySelector('#viewPanier .panier .tax .final-price .nb').innerHTML = panier['price_ttc'].toFixed(2)+"€";
            }
        }
    );
}


