{% set trash = helper('IconSvg::render', 'trash', 15, 15) %}
<div class="uk-flex uk-flex-middle uk-flex-wrap" uk-margin>
    {% if uploadDirs %}
        {% for dir in uploadDirs %}
            <a class="upload-dir uk-text-small uk-text-truncate" href="{{ uri.routeTo('/' ~ dir) }}">
                <span uk-icon="icon: folder; ratio: 1.5"></span>
                <br/>{{ dir }}
                <button type="button">{{ trash }}</button>
            </a>
        {% endfor %}
    {% endif %}

    {% if uploadFiles %}
        {% for file in uploadFiles %}
            {% set image = helper('Image::loadImage', uploadPath ~ '/' ~ file.file) %}
            {% if image %}
                {% set name = basename(file.file) %}
                <a class="upload-file image" href="{{ uri.routeTo(name) }}"
                   uk-tooltip="{{ name | escape_attr }}">
                    <img class="uk-position-center" src="{{ image.getUri() }}" alt=""/>
                    <button type="button">{{ trash }}</button>
                </a>
            {% endif %}
        {% endfor %}
    {% endif %}
</div>
