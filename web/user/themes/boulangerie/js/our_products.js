const seeMore = document.getElementById("btn_see_more");
const ourProduct = document.getElementById("our_product");

seeMore.addEventListener("click",function(){

    const body = document.querySelector("body");
    
    let space = 50;

    if(body.clientWidth > 559)
    {
       space = 100;
    } 

    window.scroll({
        top: ourProduct.offsetTop - space, 
        left: 0, 
        behavior: 'smooth'
      });

});