function marqueeWidth() {
    const marquee = document.querySelectorAll('.marquee');
    if (marquee) {
        setTimeout(() => {
            Array.prototype.forEach.call(marquee, function (marqueeItem) {
                const marqueeMove = marqueeItem.querySelector('.marquee-inner');
                let marqueeMoveChildWidth = marqueeMove.childNodes[1].offsetWidth * marqueeMove.children.length / 2;
                marqueeMove.style.width = marqueeMoveChildWidth + 'px';
                marqueeMove.classList.add('animate');
            });
        }, 1500);
    }
}

(function($) {

    $(document).ready(function($){
        marqueeWidth();
    });

})(jQuery);
