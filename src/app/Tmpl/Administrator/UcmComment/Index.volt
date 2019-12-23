{% set paginate = paginator.paginate() %}
{%- macro rating(star, value) %}
    {% set class = '' %}

    {% if value >= star %}
        {% set class = class ~ 'uk-text-warning ' %}
    {% endif %}

    {% if value == star %}
        {% set class = class ~ 'active' %}
    {% endif %}

    <span class="{{ class | trim }}" uk-icon="star"></span>

{% endmacro %}
<form id="admin-list-form" action="{{ uri.getActive() }}" method="post">
    {{ searchTools }}
    <div class="uk-overflow-auto">
        <table class="uk-table uk-table-small uk-table-hover uk-table-middle uk-table-divider">
            <thead>
            <tr>
                <th class="uk-table-shrink uk-text-nowrap">
                    <input class="uk-checkbox check-all" type="checkbox"/>
                </th>
                <th class="uk-table-shrink uk-text-nowrap">
                    {{ partial('Grid/Sort', ['text': _('state'), 'column': 'state', 'activeOrder' : activeOrder]) }}
                </th>
                <th class="uk-table-expand uk-text-nowrap">
                    {{ partial('Grid/Sort', ['text': _('user-comment'), 'column': 'userComment', 'activeOrder' : activeOrder]) }}
                </th>
                <th class="uk-table-shrink uk-text-nowrap uk-visible@m">
                    {{ partial('Grid/Sort', ['text': _('user-name'), 'column': 'userName', 'activeOrder' : activeOrder]) }}
                </th>
                <th class="uk-table-shrink uk-text-nowrap uk-visible@m">
                    {{ partial('Grid/Sort', ['text': _('user-email'), 'column': 'userEmail', 'activeOrder' : activeOrder]) }}
                </th>

                <th class="uk-table-shrink uk-text-nowrap uk-visible@m">
                    {{ partial('Grid/Sort', ['text': _('created-at'), 'column': 'createdAt', 'activeOrder' : activeOrder]) }}
                </th>
                <th class="uk-text-nowrap uk-visible@m">
                    {{ partial('Grid/Sort', ['text': _('ID'), 'column': 'id', 'activeOrder' : activeOrder]) }}
                </th>
            </tr>
            </thead>
            <tbody>
            {% for item in paginate.items %}
                <tr>
                    <td>
                        <input class="uk-checkbox" type="checkbox"
                               name="cid[]" value="{{ item.id }}"/>
                    </td>
                    <td class="uk-text-nowrap">
                        {{ partial('Grid/Status', ['item': item]) }}
                    </td>
                    <td>
                        {% if item.isCheckedIn() %}
                            {{ partial('Grid/CheckedIn', ['item': item, 'title': helper('StringHelper::truncate', item.userComment)]) }}
                        {% else %}
                            <a class="uk-link-reset" href="{{ uri.routeTo('edit/' ~ item.id) }}">
                                {{ helper('StringHelper::truncate', item.userComment) }}
                            </a>
                        {% endif %}

                        {% if (item.parent) %}
                            <a class="uk-display-block uk-margin-left uk-text-emphasis uk-text-small" href="{{ uri.routeTo('edit/' ~ item.parent.id) }}">
                                <span uk-icon="icon: reply"></span>
                                {{ _('in-reply-to-user', ['userName': item.parent.userName]) }}
                            </a>
                        {% endif %}

                        {% if item.userVote > 0 %}
                            <div class="uk-flex uk-flex-middle">
                                <div class="uk-margin-small-right">
                                    <ul class="uk-iconnav">
                                        <li>
                                            {{ rating(1, item.userVote) }}
                                        </li>
                                        <li>
                                            {{ rating(2, item.userVote) }}
                                        </li>
                                        <li>
                                            {{ rating(3, item.userVote) }}
                                        </li>
                                        <li>
                                            {{ rating(4, item.userVote) }}
                                        </li>
                                        <li>
                                            {{ rating(5, item.userVote) }}
                                        </li>
                                    </ul>
                                </div>
                            </div>
                        {% endif %}
                    </td>
                    <td class="uk-text-nowrap uk-visible@m">
                        {{ item.userName }}
                    </td>
                    <td class="uk-text-nowrap uk-visible@m">
                        {{ item.userEmail }}
                    </td>
                    <td class="uk-text-nowrap uk-visible@m">
                        {{ helper('Date::relative', item.createdAt) }}
                    </td>
                    <td class="uk-text-nowrap uk-visible@m">
                        {{ item.id }}
                    </td>
                </tr>
            {% endfor %}
            </tbody>
        </table>
    </div>
    {{ partial('Pagination/Pagination') }}
    <input type="hidden" name="postAction"/>
    <input type="hidden" name="entityId"/>
    {{ helper('Form::tokenInput') }}
</form>
