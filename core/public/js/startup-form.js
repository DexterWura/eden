document.addEventListener('DOMContentLoaded', function () {
  var foundersList = document.getElementById('founders-list');
  var founderAddButton = document.getElementById('founder-add');
  var founderTemplate = foundersList && foundersList.querySelector('.founder-row')
    ? foundersList.querySelector('.founder-row').cloneNode(true)
    : null;

  function updateFounderNumbers() {
    document.querySelectorAll('#founders-list .founder-card-num').forEach(function (element, index) {
      element.textContent = 'Founder ' + (index + 1);
    });
  }

  function resetFounderRow(row) {
    row.querySelectorAll('input').forEach(function (input) {
      input.value = '';
    });
    row.querySelectorAll('img').forEach(function (image) {
      var preview = image.parentElement;
      if (preview) {
        preview.remove();
      }
    });
  }

  if (founderAddButton && foundersList && founderTemplate) {
    founderAddButton.addEventListener('click', function () {
      var row = founderTemplate.cloneNode(true);
      resetFounderRow(row);
      foundersList.appendChild(row);
      updateFounderNumbers();
    });
  }

  if (foundersList) {
    foundersList.addEventListener('click', function (event) {
      var removeButton = event.target.closest('.founder-remove');
      if (removeButton) {
        removeButton.closest('.founder-row').remove();
        updateFounderNumbers();
      }
    });
  }

  var seekingCheck = document.getElementById('seeking_investors');
  var fundingFields = document.getElementById('funding-round-fields');
  if (seekingCheck && fundingFields) {
    seekingCheck.addEventListener('change', function () {
      fundingFields.style.display = seekingCheck.checked ? '' : 'none';
    });
  }

  var productImagesContainer = document.getElementById('product-images-container');
  var addProductImagesButton = document.getElementById('add-more-product-images');
  var productImagesSummary = document.getElementById('product-images-summary');

  function updateProductImagesSummary() {
    var total = 0;
    productImagesContainer.querySelectorAll('input[name="product_images[]"]').forEach(function (input) {
      total += input.files && input.files.length ? input.files.length : 0;
    });
    if (productImagesSummary) {
      productImagesSummary.style.display = total ? 'block' : 'none';
      productImagesSummary.textContent = total === 1 ? '1 image selected' : total + ' images selected';
    }
  }

  if (productImagesContainer && addProductImagesButton) {
    addProductImagesButton.addEventListener('click', function () {
      var input = document.createElement('input');
      input.type = 'file';
      input.name = 'product_images[]';
      input.accept = 'image/jpeg,image/png,image/gif,image/webp';
      input.multiple = true;
      input.className = 'dash-input product-images-input startup-form-extra-image';
      productImagesContainer.appendChild(input);
    });
    productImagesContainer.addEventListener('change', function (event) {
      if (event.target.name === 'product_images[]') {
        updateProductImagesSummary();
      }
    });
  }
});
