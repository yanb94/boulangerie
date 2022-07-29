const validate = document.getElementById("deliver");
const refund = document.getElementById("refund");

validate.addEventListener("click", function(e){
    if(!confirm("Etes vous sur de vouloir valider la commande ?"))
    {
        e.preventDefault();
    }
});

refund.addEventListener('click',function(e){
    if(!confirm("Etes vous sur de vouloir rembourser la commande ?"))
    {
        e.preventDefault();
    }
})