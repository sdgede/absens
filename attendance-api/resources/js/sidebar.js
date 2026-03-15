const sidebar = document.getElementById('sidebar');
const sidebarToggle = document.getElementById('sidebarToggle');
const sidebarClose = document.getElementById('sidebarClose')
const toggleDropdownBtn = document.getElementById('toggleDropdownBtn');
const analyticsDropdown = document.getElementById('analyticsDropdown');
const analyticsArrow = document.getElementById('analyticsArrow');
const profileBtn = document.querySelector('#profileDropdown > button');
const profileMenu = document.getElementById('profileMenu');

const currentPage = document.body.getAttribute('data-page');

if (currentPage === 'admin') {

  sidebarToggle.addEventListener('click', function (e) {
    e.stopPropagation();
    sidebar.classList.toggle('-translate-x-full');
  });
  

  sidebarClose.addEventListener('click', function () {
    sidebar.classList.toggle('md:-translate-x-full');
    sidebar.classList.toggle('md:hidden');
  
    if (sidebar.classList.contains('md:-translate-x-full')) {
      sidebar.classList.remove('md:translate-x-0');
    } else {
      sidebar.classList.add('md:translate-x-0');
    }
  });
  
  
  
  window.addEventListener('click', function (e) {
    if (!sidebar.classList.contains('-translate-x-full') && 
        !sidebar.contains(e.target) && 
        !sidebarToggle.contains(e.target)) {
      sidebar.classList.add('-translate-x-full');
    }
  });
  
  
  document.addEventListener('DOMContentLoaded', function () {
    const isDropdownOpen = localStorage.getItem('analyticsDropdownOpen') === 'true';

    if (isDropdownOpen) {
      analyticsDropdown.classList.remove('hidden');

      // Add the rotate-180 class to the arrow - remove make it more clear :D
      // analyticsArrow.classList.add('rotate-180');
    }
  });

  toggleDropdownBtn.addEventListener('click', function (e) {
    e.stopPropagation();
    const isHidden = analyticsDropdown.classList.contains('hidden');

    analyticsDropdown.classList.toggle('hidden');
    analyticsArrow.classList.toggle('rotate-180');

    localStorage.setItem('analyticsDropdownOpen', isHidden ? 'true' : 'false');
  });

  // close dropdown when clicking outside ;M

  // window.addEventListener('click', function (e) {
  //   if (
  //     !analyticsDropdown.classList.contains('hidden') &&
  //     !analyticsDropdown.contains(e.target) &&
  //     !toggleDropdownBtn.contains(e.target)
  //   ) {
  //     analyticsDropdown.classList.add('hidden');
  //     analyticsArrow.classList.remove('rotate-180');

  //     localStorage.setItem('analyticsDropdownOpen', 'true');
  //   }
  // });

  
  
}

if (currentPage === 'user' || currentPage === 'admin') {
  profileBtn.addEventListener('click', function (e) {
    e.stopPropagation();
    profileMenu.classList.toggle('hidden');
  });
  
  
  window.addEventListener('click', function (e) {
    if (!profileMenu.contains(e.target) && !profileBtn.contains(e.target)) {
      profileMenu.classList.add('hidden');
    }
  });
}
