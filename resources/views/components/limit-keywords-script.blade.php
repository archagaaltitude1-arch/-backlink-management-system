@pushOnce('scripts', 'limit-keywords')
    <script>
        document.addEventListener('DOMContentLoaded', function () {
            function keywordParts(value) {
                return String(value || '').split(',');
            }

            function filledCount(parts) {
                return parts.reduce(function (count, part) {
                    return count + (part.trim() !== '' ? 1 : 0);
                }, 0);
            }

            function limitKeywords(input) {
                var max = Number(input.getAttribute('data-max-keywords') || 5);
                var parts = keywordParts(input.value);
                var kept = [];
                var count = 0;

                for (var i = 0; i < parts.length; i++) {
                    var part = parts[i];
                    var trimmed = part.trim();
                    var isLast = i === parts.length - 1;

                    if (trimmed === '') {
                        if (isLast && count < max) {
                            kept.push(part);
                        }
                        continue;
                    }

                    if (count >= max) {
                        break;
                    }

                    kept.push(part);
                    count += 1;
                }

                var next = kept.join(',');

                if (input.value !== next) {
                    var cursor = input.selectionStart || next.length;
                    input.value = next;
                    if (typeof input.setSelectionRange === 'function') {
                        var pos = Math.min(cursor, next.length);
                        input.setSelectionRange(pos, pos);
                    }
                }

                var root = input.closest('label') || input.parentElement;
                var counter = root ? root.querySelector('[data-keyword-count]') : null;

                if (counter) {
                    counter.textContent = count + ' / ' + max;
                }
            }

            function blockExtraKeyword(event) {
                var input = event.target;
                var max = Number(input.getAttribute('data-max-keywords') || 5);
                var count = filledCount(keywordParts(input.value));
                var key = event.key || '';
                var endsWithSeparator = /,\s*$/.test(input.value);
                var isShortcut = event.ctrlKey || event.metaKey || event.altKey;
                var isEditKey = key === 'Backspace' || key === 'Delete' || key === 'ArrowLeft' || key === 'ArrowRight' || key === 'ArrowUp' || key === 'ArrowDown' || key === 'Tab' || key === 'Home' || key === 'End';

                if (count < max || isShortcut || isEditKey) {
                    return;
                }

                if (key === ',' || endsWithSeparator) {
                    event.preventDefault();
                }
            }

            document.querySelectorAll('[data-max-keywords]').forEach(function (input) {
                limitKeywords(input);
                input.addEventListener('input', function () {
                    limitKeywords(input);
                });
                input.addEventListener('keydown', blockExtraKeyword);
                input.addEventListener('paste', function () {
                    window.setTimeout(function () {
                        limitKeywords(input);
                    }, 0);
                });
            });
        });
    </script>
@endpushOnce
