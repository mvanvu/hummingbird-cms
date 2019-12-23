<div class="uk-container">
    <div class="uk-card uk-card-body">
        <h2 class="uk-text-lead">
            {{ _('hi-name', ['name': user.name]) }}
        </h2>

        <form action="{{ route('user/logout') }}" method="post">
            <button class="uk-button uk-button-primary" type="submit">
                {{ _('logout') }}
            </button>
            {{ helper('Form::tokenInput') }}
        </form>
    </div>
</div>