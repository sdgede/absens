const currentPage = document.body.getAttribute('data-page');

if (currentPage === 'auth') {
  document.addEventListener("DOMContentLoaded", function() {

    function setupPasswordToggle(toggleId, inputId, iconId) {
      const toggle = document.getElementById(toggleId);
      const input = document.getElementById(inputId);
      const icon = document.getElementById(iconId);
    
      toggle.addEventListener('click', function () {
        const isPassword = input.type === 'password';
        input.type = isPassword ? 'text' : 'password';
    
        if (isPassword) {
          icon.innerHTML = `
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
              d="M13.875 18.825A10.05 10.05 0 0112 19c-4.478 0-8.269-2.943-9.543-7a9.969 9.969 0 012.153-3.513m1.764-1.764A9.969 9.969 0 0112 5c4.477 0 8.268 2.943 9.542 7a9.956 9.956 0 01-4.043 5.362M15 12a3 3 0 11-6 0 3 3 0 016 0zM3 3l18 18" />
          `;
        } else {
          icon.innerHTML = `
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
              d="M2.458 12C3.732 7.943 7.523 5 12 5c4.477 0 8.268 2.943 9.542 7-1.274 4.057-5.065 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" />
          `;
        }
      });
    }
    
  
    setupPasswordToggle('togglePassword', 'password', 'eyeIcon');
  
    setupPasswordToggle('toggleConfirmPassword', 'confirmPassword', 'eyeIconConfirm');
  });

}