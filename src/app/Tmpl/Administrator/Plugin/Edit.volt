{% set group = pluginConfig.get('manifest.group') %}
{% set name = pluginConfig.get('manifest.name') %}
{% set title = pluginConfig.get('manifest.title', name) | lower %}
{% set desc = pluginConfig.get('manifest.description') | lower %}
{% set isCmsCore = cmsConfig.get('isCmsCore', false) %}
{% set isActive = cmsConfig.get('active', false) %}

<form id="admin-edit-form"
      action="{{ helper('Uri::getActive') }}" method="post">
    <h2 class="uk-heading-bullet">
        {{ _(title) }}
    </h2>

    <div class="uk-text-meta uk-margin">
        {{ _(desc) }}
    </div>
    {% if paramsForm.count() %}
        <div class="uk-form-horizontal uk-width-xlarge">
            {{ paramsForm.renderFields() }}
        </div>
    {% endif %}

    <input name="action" type="hidden" value="save"/>
    {{ helper('Form::tokenInput') }}
</form>