/**
 * YIC Marketplace - jQuery UI behavior and AJAX search.
 */
(function ($) {
    if (!$) {
        return;
    }

    $(function () {
        function convertToEnglishDigits(value) {
            const arabicDigits = ['٠','١','٢','٣','٤','٥','٦','٧','٨','٩'];
            const persianDigits = ['۰','۱','۲','۳','۴','۵','۶','۷','۸','۹'];

            value = String(value);

            for (let i = 0; i < 10; i++) {
                value = value.split(arabicDigits[i]).join(String(i));
                value = value.split(persianDigits[i]).join(String(i));
            }

            return value;
        }

        function cleanNumericInput(value, type) {
            value = convertToEnglishDigits(value);
            value = value.replace(/\u066B/g, '.').replace(/\u066C/g, '');

            if (type === 'decimal') {
                value = value.replace(/[^0-9.]/g, '');
                const parts = value.split('.');
                if (parts.length > 2) {
                    value = parts[0] + '.' + parts.slice(1).join('');
                }
                return value;
            }

            return value.replace(/[^0-9]/g, '');
        }

        function normalizeFormNumbers($form) {
            $form.find('input[data-numeric="integer"], input[data-numeric="decimal"], input[type="number"]').each(function () {
                const numericType = $(this).data('numeric') || 'integer';
                const oldValue = $(this).val();
                const newValue = cleanNumericInput(oldValue, numericType);

                if (oldValue !== newValue) {
                    $(this).val(newValue);
                }
            });
        }

        $(document).on('input blur', 'input[data-numeric="integer"], input[data-numeric="decimal"], input[type="number"]', function () {
            const numericType = $(this).data('numeric') || 'integer';
            const oldValue = $(this).val();
            const newValue = cleanNumericInput(oldValue, numericType);

            if (oldValue !== newValue) {
                $(this).val(newValue);
            }
        });

        $(document).on('submit', 'form', function () {
            normalizeFormNumbers($(this));
        });

        function showErrors($form, errors) {
            var $message = $form.find('.form-message');
            if (!$message.length) {
                alert(errors.join('\n'));
                return;
            }

            if (errors.length) {
                $message
                    .removeClass('success')
                    .addClass('error')
                    .html(errors.join('<br>'))
                    .slideDown(120);
            } else {
                $message.removeClass('error').empty().hide();
            }
        }

        $('#register-form').on('submit', function (event) {
            var $form = $(this);
            var errors = [];

            normalizeFormNumbers($form);

            var password = $('#reg-password').val();
            var confirmPassword = $('#confirm-password').val();
            var email = $('#reg-email').val().toLowerCase();

            if (password !== confirmPassword) {
                errors.push('Passwords do not match.');
            }

            if (password.length < 6) {
                errors.push('Password must be at least 6 characters long.');
            }

            if (!email.endsWith('@student.yic.edu.sa') && !email.endsWith('@yic.edu.sa')) {
                errors.push('Please use your official YIC college email.');
            }

            if (errors.length) {
                event.preventDefault();
                showErrors($form, errors);
            }
        });

        $('#add-item-form, #edit-item-form').on('submit', function (event) {
            var $form = $(this);
            var errors = [];

            normalizeFormNumbers($form);

            var title = $.trim($form.find('#title').val());
            var price = parseFloat($form.find('#price').val());
            var quantity = parseInt($form.find('#quantity').val(), 10);
            var fileInput = $form.find('#image')[0];
            var file = fileInput && fileInput.files ? fileInput.files[0] : null;

            if (title.length < 3) {
                errors.push('Item title must be at least 3 characters long.');
            }

            if (!price || price <= 0) {
                errors.push('Price must be a positive number.');
            }

            if (Number.isNaN(quantity) || quantity < 0) {
                errors.push('Quantity must be a valid number.');
            }

            if ($form.attr('id') === 'add-item-form' && !file) {
                errors.push('Please upload an image for your item.');
            }

            if (file && !/^image\/(jpeg|png|gif|webp)$/.test(file.type)) {
                errors.push('Only JPG, PNG, GIF, and WEBP images are allowed.');
            }

            if (errors.length) {
                event.preventDefault();
                showErrors($form, errors);
            }
        });

        $('#message-form').on('submit', function (event) {
            var $form = $(this);
            var message = $.trim($('#message_text').val());

            if (message.length < 2) {
                event.preventDefault();
                showErrors($form, ['Please write a short message before sending.']);
            }
        });

        $(document).on('click', '.js-confirm-delete, .js-confirm-action', function (event) {
            var message = $(this).data('confirm') || 'Are you sure?';
            if (!confirm(message)) {
                event.preventDefault();
            }
        });

        $(document).on('change', '.native-file-input', function () {
            var fileName = this.files && this.files.length ? this.files[0].name : 'No file chosen';
            $(this).closest('.custom-file-upload').find('.file-upload-name').text(fileName);
        });

        var $searchForm = $('#marketplace-search-form');
        if ($searchForm.length) {
            var $results = $('#marketplace-results');
            var $status = $('#search-status');
            var timer = null;

            function runSearch() {
                clearTimeout(timer);
                timer = setTimeout(function () {
                    $.ajax({
                        url: 'ajax_search_items.php',
                        method: 'GET',
                        data: $searchForm.serialize(),
                        beforeSend: function () {
                            $results.addClass('is-loading');
                            $status.text('Searching...');
                        },
                        success: function (html) {
                            var noItems = $.trim(html).indexOf('empty-grid-message') !== -1 && html.indexOf('item-card') === -1;

                            $results
                                .removeClass('is-loading')
                                .hide()
                                .html(html)
                                .fadeIn(140);
                            $status.text(noItems ? 'No items found.' : 'Results updated without refreshing the page.');
                        },
                        error: function () {
                            $results.removeClass('is-loading');
                            $status.text('Search failed. Please try again.');
                        }
                    });
                }, 220);
            }

            $('#live-search').on('input', runSearch);
            $('#category-filter').on('change', runSearch);
            $searchForm.on('submit', function (event) {
                event.preventDefault();
                runSearch();
            });

            $('#toggle-filters').on('click', function () {
                var $button = $(this);
                var expanded = $button.attr('aria-expanded') === 'true';

                $button.attr('aria-expanded', expanded ? 'false' : 'true');
                $('.category-control').slideToggle(160);
            });
        }
    });
})(window.jQuery);
