(function() {

    let buttonOpen = document.querySelector(".drawer-button")
    let hidePage = document.querySelector('.hide-page')
    let body = document.querySelector('body');
    let drawer = document.querySelector(".drawer");

    buttonOpen.addEventListener("click",function (){
        hidePage.classList.toggle('open');
        body.classList.toggle('body-drawer');
        drawer.classList.toggle('open');
    })

    hidePage.addEventListener("click",function(){
        hidePage.classList.toggle('open');
        body.classList.toggle('body-drawer');
        drawer.classList.toggle('open');
    })

})();