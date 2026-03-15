const currentPage = document.body.getAttribute('data-page');
const stars = document.querySelectorAll('#stars svg');
const ratingValue = document.getElementById('ratingValue');

if (currentPage === 'user') {
  stars.forEach((star, index1) => {
    star.addEventListener('click', () => {
  
      
      stars.forEach((star, index2) => {
        index1 >= index2 ? star.classList.add('text-yellow-500') : star.classList.remove('text-yellow-500');
      });

      const rating = star.getAttribute('data-rating');
      ratingValue.value = rating;
    });
  });
}