const menus = document.querySelectorAll('header ul');
const menuItems = Array.from(document.querySelectorAll('header ul a'));
let activeItem = document.querySelector('.active');

const indicators = [
    document.createElement('span'),
    document.createElement('span')
];
menus.forEach((menu, key) => {
    indicators[key].classList.add('indicator');
    menu.appendChild(indicators[key]);
})

if(activeItem) {
    indicators.forEach((indicator => indicator.style.setProperty('transform', getTransform(activeItem))));
}

/**
 * 
 * @param {HTMLElement} element
 * @return {string} 
 */
 function getTransform (element) {
    const transform = {
        x: element.offsetLeft,
        scaleX: element.offsetWidth / 100
    }
    return `translateX(${transform.x}px) scaleX(${transform.scaleX})`
}

/**
 * 
 * @param {{currentTarget: HTMLElement}} e 
 */
function onItemOver(e) {
    if(e.currentTarget === activeItem) {
        return;
    }

    activeItem?.classList.remove('active');
    e.currentTarget.classList.add('active');

    indicators.forEach((indicator) => {
        indicator.animate([
            {transform: getTransform(e.currentTarget)}
        ], {
            fill: 'both',
            duration: 600,
            easing: 'cubic-bezier(.48,1.55,.28,1)'
        });
    })
    activeItem = e.currentTarget;
}

menuItems.forEach((item) => {
    item.addEventListener('mouseover', onItemOver);
})