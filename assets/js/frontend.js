document.addEventListener('DOMContentLoaded', function() {

  let ratings = {};

  // Star click
  document.querySelectorAll('.imcr-stars').forEach(starContainer => {
    starContainer.addEventListener('click', function(e) {
      let starEl = e.target.closest('.imcr-star');
      if (!starEl) return;

      let field = this.closest('.imcr-criteria').dataset.field;
      let value = parseInt(starEl.dataset.value);
      ratings[field] = value;

      // Highlight stars
      this.querySelectorAll('.imcr-star').forEach(star => {
        star.classList.toggle('selected', parseInt(star.dataset.value) <= value);
      });
    });
  });

  // Submit
  const submitBtn = document.getElementById('imcr-submit');
  if (submitBtn) {
    submitBtn.addEventListener('click', function() {
      let review = document.querySelector('#imcr-rating-box textarea').value;
      let postId = document.getElementById('imcr-rating-box').dataset.postId;
      let responseBox = document.getElementById('imcr-response');

      if (Object.keys(ratings).length === 0) {
        responseBox.innerHTML = '<span style="color:red">Please provide at least one rating.</span>';
        return;
      }

      this.innerText = 'Submitting...';
      this.disabled = true;

      // Prepare data
      let formData = new FormData();
      formData.append('action', 'submit_imcr_review');
      formData.append('post_id', postId);
      formData.append('ratings', JSON.stringify(ratings));
      formData.append('review', review);
      if (typeof imcr_ajax_obj !== 'undefined') {
        formData.append('nonce', imcr_ajax_obj.nonce);
      }

      // AJAX Request
      fetch(imcr_ajax_obj.ajax_url, {
        method: 'POST',
        body: formData
      })
      .then(response => response.json())
      .then(data => {
        if (data.success) {
          responseBox.innerHTML = '<span style="color:green">' + data.data.message + '</span>';
          setTimeout(() => window.location.reload(), 2000); // Reload to show the new review
        } else {
          responseBox.innerHTML = '<span style="color:red">' + (data.data.message || 'Error occurred.') + '</span>';
          submitBtn.innerText = 'Submit Rating';
          submitBtn.disabled = false;
        }
      })
      .catch(error => {
        responseBox.innerHTML = '<span style="color:red">An unexpected error occurred.</span>';
        console.error(error);
        submitBtn.innerText = 'Submit Rating';
        submitBtn.disabled = false;
      });
    });
  }

});
