<form class="uk-form-horizontal" id="admin-edit-form" method="post" action="{{ uri.routeTo('save') }}">
    <div class="config-container">
        <div uk-grid>
            <div class="uk-width-auto@m uk-width-1-5@l">
                <ul class="uk-tab-left" uk-tab="connect: #config-id">
                    <li>
                        <a href="#">
                            {{ _('site') }}
                        </a>
                    </li>
                    <li>
                        <a href="#">
                            {{ _('locale') }}
                        </a>
                    </li>
                    <li>
                        <a href="#">
                            {{ _('users') }}
                        </a>
                    </li>
                    <li>
                        <a href="#">
                            {{ _('system') }}
                        </a>
                    </li>
                </ul>
            </div>
            <div class="uk-width-3-4@m uk-width-4-5@l">
                <ul id="config-id" class="uk-switcher">
                    <li class="config-site">
                        {{ formsManager.get('site').renderFields() }}
                    </li>
                    <li class="config-locale">
                        {{ formsManager.get('locale').renderFields() }}
                    </li>
                    <li class="config-users">
                        {{ formsManager.get('user').renderFields() }}
                    </li>
                    <li class="config-system">
                        {{ formsManager.get('system').renderFields() }}
                        <div class="uk-margin">
                            <button class="uk-button uk-button-primary uk-button-small" id="btn-send-test-mail"
                                    type="button">
                                <span uk-icon="send"></span>
                                {{ _('send-test-mail') | escape }}
                            </button>
                        </div>
                    </li>
                </ul>
            </div>
        </div>
        {{ helper('Form::tokenInput') }}
    </div>
</form>
