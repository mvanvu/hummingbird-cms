(function ($) {
    $(document).on('focus', '.input-emoji', function () {
        var input = $(this);

        if (input.hasClass('emoji-initialised')) {
            return;
        }

        input.addClass('emoji-initialised');
        var
            emojiUnicode = [128512, 128513, 128514, 129315, 128515, 128516, 128517, 128518, 128521, 128522, 128523, 128526, 128525, 128536, 129392, 128535, 128537, 128538, 128578, 129303, 129321, 129300, 129320, 128528, 128529, 128566, 128580, 128527, 128547, 128549, 128558, 129296, 128559, 128554, 128555, 128564, 128524, 128539, 128540, 128541, 129316, 128530, 128531, 128532, 128533, 128579, 129297, 128562, 128577, 128534, 128542, 128543, 128548, 128546, 128557, 128550, 128551, 128552, 128553, 129327, 128556, 128560, 128561, 129397, 129398, 128563, 129322, 128565, 128545, 128544, 129324, 128567, 129298, 129301, 129314, 129326, 129319, 128519, 129312, 129313, 129395, 129396, 129402, 129317, 129323, 129325, 129488, 129299, 129309, 128077, 128078, 128074, 9994],
            parentContainer = $('<div class="uk-position-relative emoji-input-container"/>'),
            icon = $('<a href="javascript:void(0)" style="font-size: 20px"/>'),
            drop = $('<div class="uk-width-medium uk-background-default uk-padding-small" uk-drop="mode: click; pos: bottom-right"/>'),
            dropHTML = '';

        icon.text(String.fromCodePoint(128524));

        if (input[0].nodeName === 'TEXTAREA') {
            icon.addClass('emoji-pick uk-link-reset uk-position-bottom-right uk-position-small');
        } else {
            icon.addClass('emoji-pick uk-link-reset uk-form-icon uk-form-icon-flip');
        }

        input.parent().append(parentContainer);
        parentContainer.append(icon);
        parentContainer.append(drop);
        parentContainer.append(input);
        emojiUnicode.forEach(function (emoji) {
            dropHTML += '<a class="uk-link-reset" style="font-size: 18px">' + String.fromCodePoint(emoji) + '</a>';
        });
        drop.html(dropHTML);
        drop.find('a').on('click', function (event) {
            event.preventDefault();
            event.stopPropagation();
            input.focus();

            // Firefox (non-standard method)
            if (!document.execCommand('insertText', false, this.innerText)
                && typeof input.setRangeText === 'function') {
                var start = input.selectionStart;
                input.setRangeText(this.innerText);
                // update cursor to be at the end of insertion
                input.selectionStart = input.selectionEnd = start + this.innerText.length;
            }

            input.trigger('change');
        });

        // Revert focus
        input.focus();
    });

})(jQuery);