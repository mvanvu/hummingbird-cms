{{ helper('Asset::addFile', 'user-forgot.js') | void }}

<div class="uk-container">
    <div class="uk-margin-top uk-margin-bottom">
        <form class="uk-form-horizontal" action="{{ route('user/request') }}" method="post">
            <div class="uk-panel uk-padding uk-background-muted">
                <div class="uk-text-muted uk-h5">
                    <div id="user-reset-pwd-desc">
                        {{ _('user-reset-pwd-desc') }}
                    </div>
                    <div class="uk-hidden" id="user-remind-username-desc">
                        {{ _('user-remind-username-desc') }}
                    </div>
                </div>

                <div class="uk-margin">
                    <label class="uk-form-label" for="reset-email">
                        {{ _('enter-your-email-address') ~ '*' }}
                    </label>
                    <div class="uk-form-controls">
                        <div class="uk-inline">
                            <input class="uk-input uk-margin-small" id="reset-email" name="email" type="email"
                                   autocomplete="off" autofocus
                                   required/>
                            <div class="uk-margin-small uk-flex">
                                <label>
                                    <input class="uk-radio" type="radio" name="requestType" value="P" checked/>
                                    {{ _('forgot-password') }}
                                </label>
                                <label class="uk-margin-small-left">
                                    <input class="uk-radio" type="radio" name="requestType" value="U"/>
                                    {{ _('forgot-username') }}
                                </label>
                            </div>
                            <button class="uk-button uk-button-primary" type="submit">
                                {{ _('send-request') }}
                            </button>
                        </div>
                    </div>
                </div>
            </div>
            {{ helper('Form::tokenInput') }}
        </form>
    </div>
</div>