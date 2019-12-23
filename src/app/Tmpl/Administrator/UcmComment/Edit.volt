{% set form = formsManager.get('general') %}
<form id="admin-edit-form" action="{{ uri.routeTo('edit/' ~ form.getField('id').getValue()) }}" method="post">
    {{ form.renderField('id') }}
    {{ form.renderField('referenceContext') }}
    {{ form.renderField('parentId') }}
    {{ form.renderField('userIp') }}

    <div class="uk-grid-small uk-grid-divider" uk-grid>
        <div class="uk-with-3-4@m uk-width-2-3@s">
            <div class="uk-grid-small uk-child-width-1-2@s" uk-grid>
                <div>
                    {{ form.renderField('userName') }}
                </div>
                <div>
                    {{ form.renderField('userEmail') }}
                </div>
            </div>
            {{ form.renderField('userComment') }}
        </div>

        <div class="uk-with-1-4@m uk-width-1-3@s">
            {{ form.renderField('state') }}
            {{ form.renderField('userVote') }}
        </div>
    </div>
    {{ helper('Form::tokenInput') }}
</form>
