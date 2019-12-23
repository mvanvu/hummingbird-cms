<div class="uk-margin" data-menu-type="{{ menuType }}">
    <div class="uk-padding-small uk-background-muted">
        <div class="uk-flex uk-flex-middle uk-margin">
            <input class="uk-input uk-form-small uk-width-medium" name="menuName" type="text"
                   value="{{ menuType }}"/>
            <button class="uk-button uk-button-small uk-button-primary btn-menu-type-rename dd-nodrag" type="button">
                <span uk-icon="icon: check"></span>
            </button>

            <button class="uk-button uk-button-small uk-button-danger uk-flex-right btn-menu-type-remove dd-nodrag"
                    type="button">
                <span uk-icon="icon: trash"></span>
            </button>
        </div>
        <div class="uk-padding-small">
            <div class="dd nestable">
                {{ helper('Menu::outputNestableList', menuType) }}
            </div>
        </div>
    </div>
</div>
