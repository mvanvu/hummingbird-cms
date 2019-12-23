<div id="media-container" data-token="{{ helper('Form::getToken') | escape_attr }}"
     data-sub-dirs="{{ ltrim(subDirs, '/') | escape_attr }}">
    <div class="actions-container uk-margin">
        <ul class="uk-subnav uk-subnav-divider">
            <li>
                <a class="uk-text-primary" href="#" uk-toggle="target: #toggle-folder">
                    <span uk-icon="plus"></span>
                    {{ _('new-folder') }}
                </a>
            </li>
        </ul>
        <div class="uk-margin" id="toggle-folder" hidden>
            <div class="uk-inline">
                <a class="uk-form-icon uk-form-icon-flip" href="#" uk-icon="icon: check"></a>
                <input class="uk-input" type="text" placeholder="{{ _('enter-folder-name') | escape_attr }}"
                       onkeyup="this.value = this.value.replace(/[^a-z0-9]/gi, ''); if (event.key === 'Enter' && this.value.length) document.querySelector('#toggle-folder a').click();"/>
            </div>
        </div>
        {% if subDirs is not empty %}
            <ul class="uk-breadcrumb">
                <li>
                    <a href="{{ uri.routeTo('media/index') }}">
                        {{ helper('IconSvg::render', 'cloud-upload', 20, 20) }}
                    </a>
                </li>
                {% set baseDir = null %}
                {% for subDir in explode('/', subDirs) %}
                    {% if baseDir %}
                        {% set baseDir = baseDir ~ '/' ~ subDir %}
                    {% else %}
                        {% set baseDir = subDir %}
                    {% endif %}
                {% endfor %}

                <li>
                    <a href="{{ uri.routeTo('media/index/' ~ baseDir) }}">
                        {{ subDir }}
                    </a>
                </li>
            </ul>
        {% endif %}
    </div>

    <div class="upload-assets-container">
        {% if uploadDirs is not empty or uploadFiles is not empty %}
            {{ partial('Media/Media') }}
        {% endif %}
    </div>

    <div class="js-upload uk-placeholder uk-text-center">
        <span uk-icon="icon: cloud-upload"></span>
        <span class="uk-text-middle">{{ _('media-upload-hint') }}</span>
        <div uk-form-custom>
            <input type="file" accept="image/*" multiple/>
            <span class="uk-link">{{ _('media-upload-select') }}</span>
        </div>
    </div>

    <progress id="js-progressbar" class="uk-progress" value="0" max="100" hidden></progress>
</div>
