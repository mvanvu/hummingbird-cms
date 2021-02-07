<div class="uk-width-small" data-file-base="{{ file['base'] | escape_attr }}" data-file-url="{{ file['url'] }}">
    {% if file['isImage'] %}
        <div class="uk-position-relative">
            <div class="uk-cover-container uk-height-small" uk-lightbox>
                <a class="uk-link-reset" href="{{ file['url'] }}" target="_blank">
                    <img data-src="{{ file['url'] }}" alt="{{ file['name'] | escape_attr }}"
                         uk-cover uk-img>
                </a>
            </div>
            <a class="remove uk-link uk-link-icon uk-background-default uk-text-danger uk-position-top-right uk-position-z-index"
               uk-icon="icon: close"></a>
        </div>
    {% else %}
        <div class="uk-height-small uk-position-relative uk-card uk-card-default uk-card-small uk-card-body uk-flex uk-flex-column uk-flex-center uk-flex-middle">
            <div uk-icon="icon: cloud-download; ratio: 2"></div>
            <a class="uk-link-muted uk-text-small" href="{{ file['url'] }}" target="_blank">
                {{ file['name'] }}
            </a>
            <a class="remove uk-link uk-link-icon uk-background-default uk-text-danger uk-position-top-right uk-position-z-index"
               uk-icon="icon: close"></a>
        </div>
    {% endif %}
</div>