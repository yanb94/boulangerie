class PanierCallBack
{
    static toggleElement() {
        document.querySelector('.hide-panier').classList.toggle("open");
        document.getElementById('viewPanier').classList.toggle("open");
        document.querySelector('body').classList.toggle('body-panier');
    }

    static productAct() {
        const product = this.getAttribute("data-product");
        const action = this.getAttribute("data-product-action");
        
        if(action == 'add')
        {
            PanierAjax.addToPanier(product).then(
                (data) => {
                    loadPanier();
                }
            );
        }
        else if(action == 'decrease')
        {
            PanierAjax.decreaseQuantity(product).then(
                (data) => {
                    loadPanier();
                }
            )
        }
        else if(action == 'clearProduct')
        {
            PanierAjax.clearProduct(product).then(
                (data) => {
                    loadPanier();
                }
            )
        }
    }

    static productChange()
    {
        const product = this.getAttribute("data-product");
        const quantity = this.value;
    
        PanierAjax.setProductQuantity(product,quantity).then(
            (data) => {
                loadPanier();
            }
        );
    }

    static panierClear()
    {
        PanierAjax.clearAll().then(
            (data) => {
                loadPanier();
            }
        )
    }
}