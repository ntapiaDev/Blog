const fixedNav = document.querySelector('.fixed-nav');

const sectionDestinations = document.querySelector('#destinations');

document.addEventListener('scroll', () => {
    if(sectionDestinations.getBoundingClientRect().y < 85) {
        fixedNav.style.display = "flex";
        fixedNav.style.opacity = (100 - sectionDestinations.getBoundingClientRect().y) / 100;
        console.log('down');
    } else if(sectionDestinations.getBoundingClientRect().y > 85) {
        fixedNav.style.display = "none";
        console.log('up');
    }
})
