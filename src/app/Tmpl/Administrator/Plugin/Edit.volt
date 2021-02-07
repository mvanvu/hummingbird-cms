{% set manifest = registry(model.manifest) %}
{% set group = manifest.get('group') %}
{% set name = manifest.get('name') %}
{% set title = manifest.get('title', name) | lower %}
{% set desc = manifest.get('description', '') | lower %}

<form id="admin-edit-form" action="{{ currentLink() }}" method="post">
    <h2 class="uk-heading-bullet">
        {{ _(title) }}
    </h2>

    {% if desc is not empty %}
        <div class="uk-text-meta uk-margin">
            {{ _(desc) }}
        </div>
    {% endif %}

    {% if paramsForm.count() %}
        <div class="uk-form-horizontal uk-width-xlarge">
            {{ paramsForm.renderFields() }}
        </div>
    {% endif %}

    <input name="action" type="hidden" value="save"/>
    {{ csrfInput() }}
</form>