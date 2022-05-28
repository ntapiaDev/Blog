const menus = document.querySelectorAll('header ul');
const menuItems = Array.from(document.querySelectorAll('header ul a'));
let activeItem = document.querySelector('.active');

let indicators = [];
menus.forEach((menu) => {
    const indicator = document.createElement('span');
    indicators.push(indicator);
    indicator.classList.add('indicator');
    menu.appendChild(indicator);
})

if(activeItem) {
    indicators.forEach((indicator => indicator.style.setProperty('transform', getTransform(activeItem))));
}

/**
 * 
 * @param {HTMLElement} element
 * @return {string} 
 */
 function getTransform(element) {
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
    changeIndicator(e.currentTarget);
}

menuItems.forEach((item) => {
    item.addEventListener('mouseover', onItemOver);
    item.addEventListener('mouseout', updateIndicator);
})

/**
 * 
 * @param {HTMLElement} element 
 */
function changeIndicator(element) {
    if(activeItem.dataset.color === 'light') {
        activeItem.classList.remove('active-light');
        activeItem.classList.remove('active-dark');
        element.classList.add('active-light');
    } else {
        activeItem.classList.remove('active-dark');
        activeItem.classList.remove('active-light');
        element.classList.add('active-dark');
    }
    activeItem.classList.remove('active');
    element.classList.add('active');
    indicators.forEach((indicator) => {
        indicator.animate([
            {transform: getTransform(element)}
        ], {
            fill: 'both',
            duration: 600,
            easing: 'cubic-bezier(.48,1.35,.28,1)'
        });
    })
    activeItem = element;
}

//Menu on scroll
const destinations = document.querySelector('#destinations');
const contact = document.querySelector('#contact');

function updateIndicator() {
    if(destinations.getBoundingClientRect().y > 100 && activeItem != menuItems[0]) {
        if(activeItem !== menuItems[1] && activeItem !== menuItems[2] && activeItem !== menuItems[3])
        activeItem = menuItems[0];
        changeIndicator(menuItems[0]);
    } else if(destinations.getBoundingClientRect().y < 100 && contact.getBoundingClientRect().y > 100 && activeItem != menuItems[1]) {
        changeIndicator(menuItems[5]);
    } else if (contact.getBoundingClientRect().y < 100 && activeItem != menuItems[3]){
        changeIndicator(menuItems[7]);
    }
}

document.addEventListener('scroll', updateIndicator);
updateIndicator();