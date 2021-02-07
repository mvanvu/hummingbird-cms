{% set trash = helper('IconSvg::render', 'trash', 15, 15) %}
<div class="uk-flex uk-flex-middle uk-flex-wrap" uk-margin>
    {% if uploadDirs %}
        <div class="uk-flex uk-flex-middle uk-flex-wrap uk-margin-small-right">
            {% for dir in uploadDirs %}
                <a class="upload-dir uk-text-small uk-text-truncate" href="{{ uri.routeTo('/' ~ dir) }}"
                   data-path="{{ uri.routeTo('/' ~ dir) }}"
                >
                    <span uk-icon="icon: folder; ratio: 1.5"></span>
                    <br/>{{ dir }}
                    <button type="button">{{ trash }}</button>
                </a>
            {% endfor %}
        </div>
    {% endif %}

    {% if uploadFiles %}
        <div class="uk-flex uk-flex-middle uk-flex-wrap"{{ isRaw ? '' : ' uk-lightbox' }}>
            {% for file in uploadFiles %}
                {% set image = helper('Image::loadImage', uploadPath ~ '/' ~ file.file) %}
                {% if image %}
                    {% set name = basename(file.file) %}
                    <a class="upload-file image" href="{{ image.getUri(true) }}"
                       data-path="{{ uri.routeTo('?file=' ~ name) }}"
                       uk-tooltip="{{ name | escape_attr }}">
                        <img class="uk-position-center" src="{{ image.getUri(true) }}" alt="" uk-img/>
                        <button type="button">{{ trash }}</button>
                    </a>
                {% endif %}
            {% endfor %}
        </div>
    {% endif %}
</div>
