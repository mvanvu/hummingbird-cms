<div class="tpl-tree-container uk-padding uk-background-muted uk-box-shadow-small" id="{{ field['id'] }}">
    <div class="actions-container uk-margin" data-sub-dirs="{{ subDirs }}">
        <ul class="uk-subnav uk-subnav-divider">
            <li>
                <a class="uk-text-primary" href="#" uk-toggle="target: #{{ field['id'] }} .toggle">
                    <span uk-icon="plus"></span>
                    {{ _('new') }}
                </a>
            </li>
        </ul>
        <div class="uk-margin toggle" hidden>
            <div class="uk-inline">
                <a class="uk-form-icon uk-form-icon-flip" href="#" uk-icon="icon: check"></a>
                <input class="uk-input" type="text"
                       onkeyup="this.value = this.value.replace(/[^a-z0-9_\-\.]/gi, ''); if (event.key === 'Enter' && this.value.length) document.querySelector('#{{ field['id'] }} .toggle a').click();"/>
            </div>
        </div>
        {% if subDirs is not empty %}
            <ul class="uk-breadcrumb">
                <li>
                    <a data-type="folder" uk-icon="icon: home"></a>
                </li>
                {% set baseDir = null %}
                {% for subDir in explode('/', subDirs) %}
                    {% if baseDir %}
                        {% set baseDir = baseDir ~ '/' ~ subDir %}
                    {% else %}
                        {% set baseDir = subDir %}
                    {% endif %}
                    <li>
                        <a data-type="folder" data-source="{{ baseDir }}">
                            {{ subDir }}
                        </a>
                    </li>
                {% endfor %}
            </ul>
        {% endif %}
    </div>

    <div class="tpl-assets-container">
        {% if folders is not empty OR files is not empty %}
            <ul class="uk-list uk-list-divider">
                {% if folders %}
                    {% for dir in folders %}
                        {% set name = basename(dir) %}
                        <li>
                            <div class="uk-grid-small" uk-grid>
                                <div class="uk-width-expand">
                                    <a class="uk-text-small uk-text-truncate uk-text-emphasis" data-type="folder"
                                       data-source="{{ subDirs ~ '/' ~ name }}" data-name="{{ name }}">
                                        <span uk-icon="icon: folder"></span>
                                        {{ name }}
                                    </a>
                                </div>
                                <div class="uk-width-auto">
                                    <ul class="uk-iconnav">
                                        <li><a class="btn-rename" href="#" uk-icon="icon: pencil"></a></li>
                                        <li><a class="btn-remove" href="#" uk-icon="icon: close"></a></li>
                                    </ul>
                                </div>
                            </div>
                        </li>
                    {% endfor %}
                {% endif %}

                {% if files %}
                    {% for file in files %}
                        {% set name = basename(file) %}
                        <li>
                            <div class="uk-grid-small" uk-grid>
                                <div class="uk-width-expand">
                                    <a class="uk-text-small uk-text-truncate uk-text-emphasis" data-type="file"
                                       data-source="{{ subDirs ~ '/' ~ name }}" data-name="{{ name }}">
                                        <span uk-icon="icon: file-text"></span>
                                        {{ name }}
                                    </a>
                                </div>
                                <div class="uk-width-auto">
                                    <ul class="uk-iconnav">
                                        <li><a class="btn-rename" href="#" uk-icon="icon: pencil"></a></li>
                                        <li><a class="btn-remove" href="#" uk-icon="icon: close"></a></li>
                                    </ul>
                                </div>
                            </div>
                        </li>
                    {% endfor %}
                {% endif %}
            </ul>
        {% endif %}
    </div>
</div>
