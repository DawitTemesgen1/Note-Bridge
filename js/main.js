/**
 * ============================================================
 * main.js — NoteBridge Client-Side JavaScript
 * ============================================================
 * Handles: mobile menu toggle, dropdown, scroll effects,
 * file upload preview, star rating, search filters, tabs.
 * ============================================================
 */

// Wait until the page is fully loaded before running scripts
document.addEventListener('DOMContentLoaded', function () {

    // ===========================
    // 1. MOBILE NAVIGATION TOGGLE
    // ===========================
    var navToggle = document.getElementById('navToggle');
    var navLinks  = document.getElementById('navLinks');

    if (navToggle && navLinks) {
        navToggle.addEventListener('click', function () {
            navLinks.classList.toggle('open');

            // Change icon between bars and X
            var icon = navToggle.querySelector('i');
            if (navLinks.classList.contains('open')) {
                icon.classList.remove('fa-bars');
                icon.classList.add('fa-times');
            } else {
                icon.classList.remove('fa-times');
                icon.classList.add('fa-bars');
            }
        });
    }

    // ===========================
    // 2. USER DROPDOWN TOGGLE
    // ===========================
    var dropdownBtn  = document.getElementById('userDropdownBtn');
    var dropdownMenu = document.getElementById('userDropdownMenu');

    if (dropdownBtn && dropdownMenu) {
        dropdownBtn.addEventListener('click', function (e) {
            e.stopPropagation();
            dropdownMenu.classList.toggle('show');
        });

        // Close dropdown when clicking anywhere else
        document.addEventListener('click', function () {
            dropdownMenu.classList.remove('show');
        });
    }

    // ===========================
    // 3. NAVBAR SCROLL EFFECT
    // ===========================
    var navbar = document.getElementById('navbar');

    if (navbar) {
        window.addEventListener('scroll', function () {
            if (window.scrollY > 20) {
                navbar.classList.add('scrolled');
            } else {
                navbar.classList.remove('scrolled');
            }
        });
    }

    // ===========================
    // 4. FLASH MESSAGE AUTO-HIDE
    // ===========================
    var flashMessage = document.getElementById('flashMessage');

    if (flashMessage) {
        setTimeout(function () {
            flashMessage.style.opacity = '0';
            flashMessage.style.transform = 'translateY(-10px)';
            setTimeout(function () {
                flashMessage.remove();
            }, 300);
        }, 5000); // Hide after 5 seconds
    }

    // ===========================
    // 5. FILE UPLOAD DRAG & DROP + PREVIEW
    // ===========================
    var uploadArea    = document.getElementById('uploadArea');
    var fileInput     = document.getElementById('fileInput');
    var uploadPreview = document.getElementById('uploadPreview');

    if (uploadArea && fileInput) {
        // Click the area to open file browser
        uploadArea.addEventListener('click', function () {
            fileInput.click();
        });

        // Drag-and-drop visual feedback
        uploadArea.addEventListener('dragover', function (e) {
            e.preventDefault();
            uploadArea.classList.add('dragover');
        });

        uploadArea.addEventListener('dragleave', function () {
            uploadArea.classList.remove('dragover');
        });

        uploadArea.addEventListener('drop', function (e) {
            e.preventDefault();
            uploadArea.classList.remove('dragover');

            // Transfer the dropped file to the hidden input
            if (e.dataTransfer.files.length > 0) {
                fileInput.files = e.dataTransfer.files;
                showFilePreview(e.dataTransfer.files[0]);
            }
        });

        // Show preview when file is selected
        fileInput.addEventListener('change', function () {
            if (fileInput.files.length > 0) {
                showFilePreview(fileInput.files[0]);
            }
        });
    }

    /**
     * Display the selected file name and size below the upload area.
     * @param {File} file — the selected file object
     */
    function showFilePreview(file) {
        if (!uploadPreview) return;

        var fileName = uploadPreview.querySelector('.file-info span');
        var fileSize = uploadPreview.querySelector('.file-info small');

        if (fileName) fileName.textContent = file.name;
        if (fileSize) fileSize.textContent = formatFileSize(file.size);

        uploadPreview.classList.add('active');
    }

    /**
     * Convert bytes to a readable string like "2.5 MB".
     * @param {number} bytes
     * @returns {string}
     */
    function formatFileSize(bytes) {
        if (bytes === 0) return '0 Bytes';

        var units = ['Bytes', 'KB', 'MB', 'GB'];
        var i = Math.floor(Math.log(bytes) / Math.log(1024));

        return parseFloat((bytes / Math.pow(1024, i)).toFixed(2)) + ' ' + units[i];
    }

    // ===========================
    // 6. STAR RATING (interactive)
    // ===========================
    var starRating = document.querySelector('.star-rating');

    if (starRating) {
        var stars = starRating.querySelectorAll('i');
        var ratingInput = document.getElementById('ratingInput');

        stars.forEach(function (star, index) {
            // Highlight stars on hover
            star.addEventListener('mouseenter', function () {
                highlightStars(index);
            });

            // Reset to current value when mouse leaves
            star.addEventListener('mouseleave', function () {
                var currentRating = ratingInput ? parseInt(ratingInput.value) || 0 : 0;
                highlightStars(currentRating - 1);
            });

            // Set the rating on click
            star.addEventListener('click', function () {
                var value = index + 1;
                if (ratingInput) ratingInput.value = value;
                highlightStars(index);
            });
        });

        /**
         * Fill stars from 0 up to the given index.
         * @param {number} upTo — the last star index to fill
         */
        function highlightStars(upTo) {
            stars.forEach(function (s, i) {
                if (i <= upTo) {
                    s.classList.add('filled');
                    s.classList.remove('far');
                    s.classList.add('fas');
                } else {
                    s.classList.remove('filled');
                    s.classList.remove('fas');
                    s.classList.add('far');
                }
            });
        }
    }

    // ===========================
    // 7. TAB NAVIGATION (admin)
    // ===========================
    var tabButtons = document.querySelectorAll('.tab-btn');

    if (tabButtons.length > 0) {
        tabButtons.forEach(function (btn) {
            btn.addEventListener('click', function () {
                var target = btn.getAttribute('data-tab');

                // Remove active class from all tabs and content
                tabButtons.forEach(function (b) { b.classList.remove('active'); });
                document.querySelectorAll('.tab-content').forEach(function (c) {
                    c.classList.remove('active');
                });

                // Activate the clicked tab
                btn.classList.add('active');
                var tabContent = document.getElementById(target);
                if (tabContent) {
                    tabContent.classList.add('active');
                }
            });
        });
    }

    // ===========================
    // ===========================
    // 8. DYNAMIC COURSE FILTER (Cascading Department -> Course)
    // ===========================
    function setupCascadingDropdown(deptId, courseId, promptWhenEmpty, promptWhenDeptSelected) {
        var deptEl   = document.getElementById(deptId);
        var courseEl = document.getElementById(courseId);
        if (!deptEl || !courseEl) return;

        // Collect all pre-rendered options with data-dept
        var optionsList = Array.prototype.slice.call(courseEl.querySelectorAll('option[data-dept]'));
        var initialSelectedCourse = courseEl.value;

        function refreshCourses() {
            var selectedDept = deptEl.value;
            var currentVal   = courseEl.value || initialSelectedCourse;

            // Clear course options
            courseEl.innerHTML = '';

            // If no department is selected, show clean prompt
            if (!selectedDept) {
                var emptyOpt = document.createElement('option');
                emptyOpt.value = '';
                emptyOpt.textContent = promptWhenEmpty;
                emptyOpt.selected = true;
                courseEl.appendChild(emptyOpt);
                return;
            }

            // Default top option when department is selected
            var defaultOpt = document.createElement('option');
            defaultOpt.value = '';
            defaultOpt.textContent = promptWhenDeptSelected;
            courseEl.appendChild(defaultOpt);

            var matched = false;
            optionsList.forEach(function (opt) {
                if (opt.getAttribute('data-dept') === selectedDept) {
                    var clone = opt.cloneNode(true);
                    if (currentVal && String(clone.value) === String(currentVal)) {
                        clone.selected = true;
                        matched = true;
                    }
                    courseEl.appendChild(clone);
                }
            });

            if (!matched) {
                defaultOpt.selected = true;
            }
        }

        deptEl.addEventListener('change', function () {
            initialSelectedCourse = '';
            refreshCourses();
        });

        // Initialize on load
        refreshCourses();
    }

    // Initialize cascading filter on browse.php
    setupCascadingDropdown('departmentFilter', 'courseFilter', 'All Courses (Select Department to filter)', 'All Courses');

    // Initialize cascading filter on upload.php
    setupCascadingDropdown('department_select', 'course_id', '-- Select Department First --', '-- Select Course --');

    // ===========================
    // 9. FORM VALIDATION
    // ===========================
    var forms = document.querySelectorAll('form[data-validate]');

    forms.forEach(function (form) {
        form.addEventListener('submit', function (e) {
            var isValid = true;

            // Clear previous errors
            form.querySelectorAll('.form-error').forEach(function (el) {
                el.remove();
            });

            // Check required text/select inputs
            form.querySelectorAll('[required]').forEach(function (input) {
                if (!input.value.trim()) {
                    isValid = false;
                    showFieldError(input, 'This field is required.');
                }
            });

            // Check required file uploads
            var fileInput = form.querySelector('input[type="file"][name="pdf_file"]');
            if (fileInput && fileInput.files.length === 0) {
                isValid = false;
                var uploadArea = form.querySelector('#uploadArea');
                if (uploadArea) {
                    showFieldError(uploadArea, 'Please select or drop a PDF file to upload.');
                }
            }

            // Check email format
            form.querySelectorAll('[type="email"]').forEach(function (input) {
                if (input.value && !isValidEmail(input.value)) {
                    isValid = false;
                    showFieldError(input, 'Please enter a valid email address.');
                }
            });

            // Check password minimum length
            form.querySelectorAll('[data-minlength]').forEach(function (input) {
                var min = parseInt(input.getAttribute('data-minlength'));
                if (input.value && input.value.length < min) {
                    isValid = false;
                    showFieldError(input, 'Must be at least ' + min + ' characters.');
                }
            });

            // Check password confirmation matching
            var pass = form.querySelector('input[name="password"], input[name="new_password"]');
            var confirmPass = form.querySelector('input[name="confirm_password"]');
            if (pass && confirmPass && pass.value && confirmPass.value) {
                if (pass.value !== confirmPass.value) {
                    isValid = false;
                    showFieldError(confirmPass, 'Passwords do not match.');
                }
            }

            if (!isValid) {
                e.preventDefault();
            }
        });
    });

    /**
     * Display an error message below a form field.
     * @param {HTMLElement} input — the input element
     * @param {string} message   — the error text
     */
    function showFieldError(input, message) {
        var error = document.createElement('div');
        error.className = 'form-error';
        error.textContent = message;
        input.parentNode.appendChild(error);
        input.style.borderColor = 'var(--error)';

        // Reset border on focus
        input.addEventListener('focus', function () {
            input.style.borderColor = '';
            var err = input.parentNode.querySelector('.form-error');
            if (err) err.remove();
        }, { once: true });
    }

    /**
     * Simple email format validation.
     * @param {string} email
     * @returns {boolean}
     */
    function isValidEmail(email) {
        return /^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(email);
    }

    // ===========================
    // 10. BOOKMARK TOGGLE (AJAX)
    // ===========================
    var bookmarkBtns = document.querySelectorAll('.bookmark-btn');

    bookmarkBtns.forEach(function (btn) {
        btn.addEventListener('click', function (e) {
            e.preventDefault();
            var noteId = btn.getAttribute('data-note-id');

            var appBase = (typeof window.basePath !== 'undefined') ? window.basePath : '';
            var xhr = new XMLHttpRequest();
            xhr.open('POST', appBase + '/includes/toggle_bookmark.php', true);
            xhr.setRequestHeader('Content-Type', 'application/x-www-form-urlencoded');

            xhr.onload = function () {
                if (xhr.status === 200) {
                    try {
                        var response = JSON.parse(xhr.responseText);
                        if (response.error === 'Not logged in') {
                            window.location.href = appBase + '/pages/login.php';
                            return;
                        }

                        var label = btn.querySelector('.bookmark-label');
                        var icon = btn.querySelector('i');

                        if (response.status === 'added') {
                            btn.classList.add('active');
                            if (label) {
                                label.textContent = 'Bookmarked';
                            }
                            if (icon) {
                                icon.className = 'fas fa-bookmark';
                            }
                        } else if (response.status === 'removed') {
                            btn.classList.remove('active');
                            if (label) {
                                label.textContent = 'Bookmark';
                            }
                            if (icon) {
                                icon.className = 'far fa-bookmark';
                            }

                            // If on bookmarks page, animate card removal
                            var noteCard = btn.closest('.note-card');
                            if (noteCard && window.location.pathname.indexOf('bookmarks.php') !== -1) {
                                noteCard.style.transition = 'opacity 0.4s ease, transform 0.4s ease';
                                noteCard.style.opacity = '0';
                                noteCard.style.transform = 'scale(0.95)';
                                setTimeout(function () {
                                    noteCard.remove();
                                    var remaining = document.querySelectorAll('.note-card');
                                    if (remaining.length === 0) {
                                        window.location.reload();
                                    }
                                }, 400);
                            }
                        }
                    } catch (err) {
                        console.error('Bookmark error:', err);
                    }
                }
            };

            xhr.send('note_id=' + noteId);
        });
    });

    // ===========================
    // 11. CONFIRM BEFORE DELETE
    // ===========================
    var deleteButtons = document.querySelectorAll('[data-confirm]');

    deleteButtons.forEach(function (btn) {
        btn.addEventListener('click', function (e) {
            var message = btn.getAttribute('data-confirm') || 'Are you sure?';
            if (!confirm(message)) {
                e.preventDefault();
            }
        });
    });

});

// Global basePath (respects window.basePath if injected by PHP)
var basePath = (typeof window.basePath !== 'undefined') ? window.basePath : '';
