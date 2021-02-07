<form id="admin-edit-form" action="{{ uri.getActive() }}" method="post">
    <div class="permission-details uk-grid-small" uk-grid>
        <div class="uk-width-auto@s">
            <div class="uk-card uk-background-muted uk-card-body uk-card-small">
                <ul class="uk-nav-default uk-text-center" uk-nav>
                    {% for pkg, permissions in packages %}
                        <li{{ pkg === package ? ' class="uk-active"' : '' }}>
                            <a href="{{ uri.route('role/permit', ['package': pkg]) }}">{{ _(pkg ~ '-permit-title') }}</a>
                        </li>
                    {% endfor %}
                </ul>
            </div>
        </div>
        <div class="uk-width-expand">
            <ul uk-tab>
                {% for role, form in formsManager.getForms() %}
                    <li><a href="#">{{ role }}</a></li>
                {% endfor %}
            </ul>

            <ul class="uk-switcher uk-form-horizontal uk-margin">
                {% for form in formsManager.getForms() %}
                    <li>
                        {{ form.renderFields() }}
                    </li>
                {% endfor %}
            </ul>
        </div>
    </div>
    {{ csrfInput() }}
</form>