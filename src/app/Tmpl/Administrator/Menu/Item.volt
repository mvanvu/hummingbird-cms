<div class="uk-form-horizontal" id="menu-item-container">
    <div class="uk-margin">
        <a class="uk-button uk-button-small uk-button-primary btn-add-menu-item"
           uk-icon="icon: check"></a>
        <a class="uk-button uk-button-small uk-button-default btn-close-menu-item" uk-icon="icon: close"></a>
    </div>

    {% if paramsForm.count() %}
        <div class="uk-margin item-params">
            {{ paramsForm.renderFields() }}
        </div>
    {% endif %}

    <div class="uk-margin">
        {{ menuForm.getField('icon').toString() }}
    </div>

    {{ menuForm.renderField('title') }}

    <div class="uk-margin">
        <div class="uk-grid-small uk-child-width-1-2" uk-grid>
            <div>
                {{ menuForm.renderField('target') }}
            </div>
            <div>
                {{ menuForm.renderField('nofollow') }}
            </div>
        </div>
    </div>

    <div class="uk-hidden">
        {{ menuForm.getField('id').toString() }}
        {{ menuForm.getField('menu').toString() }}
        {{ menuForm.getField('type').toString() }}
    </div>
</div>