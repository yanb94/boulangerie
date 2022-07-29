const chevrons = document.querySelectorAll(".chevron");

chevrons.forEach(element => {
    element.addEventListener('click',function(){
        this.parentNode.parentNode.querySelector(".other-info-row").classList.toggle("show");
    })
});