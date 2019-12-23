<div class="uk-container">
    <div class="uk-margin-top uk-margin-bottom">
        <form class="uk-form-horizontal" action="{{ route('user/reset/' ~ token) }}" method="post">
            <div class="uk-card">
                {{ flashSession.output() }}
                <h2 class="uk-alert uk-alert-primary uk-h5">
                    {{ _('user-new-pwd-desc') }}
                </h2>
                <div class="uk-card-body uk-background-muted">
                    <div class="uk-margin">
                        <label class="uk-form-label" for="reset-password">
                            {{ _('new-pwd') ~ '*' }}
                        </label>
                        <div class="uk-form-controls">
                            <input class="uk-input uk-form-width-large" id="reset-password" name="password"
                                   type="password"
                                   autocomplete="off" autofocus
                                   required/>
                        </div>
                    </div>
                    <div class="uk-margin">
                        <label class="uk-form-label" for="reset-confirm-password">
                            {{ _('new-pwd-confirm') ~ '*' }}
                        </label>
                        <div class="uk-form-controls">
                            <input class="uk-input uk-form-width-large uk-margin-small" id="reset-confirm-password"
                                   name="confirmPassword" type="password"
                                   autocomplete="off"
                                   required/>
                            <button class="uk-button uk-button-primary uk-width-medium uk-display-block" type="submit">
                                {{ _('update-password') }}
                            </button>
                        </div>
                    </div>
                </div>
            </div>
            {{ helper('Form::tokenInput') }}
        </form>
    </div>
</div>