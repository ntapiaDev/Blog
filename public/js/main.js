const fixedNav = document.querySelector('.fixed-nav');
const sectionDestinations = document.querySelector('#destinations');

if(sectionDestinations.getBoundingClientRect().y < 100) {
    fixedNav.style.display = "flex";
    fixedNav.style.opacity = (100 - sectionDestinations.getBoundingClientRect().y) / 100;
}
document.addEventListener('scroll', () => {
    if(sectionDestinations.getBoundingClientRect().y < 100) {
        fixedNav.style.display = "flex";
        fixedNav.style.opacity = (100 - sectionDestinations.getBoundingClientRect().y) / 100;
    } else if(sectionDestinations.getBoundingClientRect().y > 100 && fixedNav.style.display !== "none") {
        fixedNav.style.display = "none";
    }
})
