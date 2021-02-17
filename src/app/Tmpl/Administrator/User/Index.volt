<form id="admin-list-form" action="{{ uri.getActive() }}" method="post">
    {{ searchTools }}
    <div class="uk-overflow-auto">
        <table class="uk-table uk-table-small uk-table-hover uk-table-middle uk-table-divider uk-table-striped">
            <thead>
            <tr>
                <th class="uk-table-shrink uk-text-nowrap">
                    <input class="uk-checkbox check-all" type="checkbox"/>
                </th>
                <th class="uk-table-expand uk-text-nowrap">
                    {{ partial('Grid/Sort', ['text': _('name'), 'column': 'name', 'activeOrder': activeOrder]) }}
                </th>
                <th class="uk-table-shrink uk-text-nowrap">
                    {{ partial('Grid/Sort', ['text': _('email'), 'column': 'email', 'activeOrder': activeOrder]) }}
                </th>
                <th class="uk-table-shrink uk-text-nowrap">
                    {{ partial('Grid/Sort', ['text': _('username'), 'column': 'username', 'activeOrder': activeOrder]) }}
                </th>
                <th class="uk-table-shrink uk-text-nowrap">
                    {{ partial('Grid/Sort', ['text': _('state'), 'column': 'active', 'activeOrder': activeOrder]) }}
                </th>
                <th class="uk-table-shrink uk-text-nowrap">
                    {{ partial('Grid/Sort', ['text': _('created-at'), 'column': 'createdAt', 'activeOrder': activeOrder]) }}
                </th>
                <th class="head-table-id uk-text-nowrap">
                    {{ partial('Grid/Sort', ['text': _('ID'), 'column': 'id', 'activeOrder': activeOrder]) }}
                </th>
            </tr>
            </thead>
            <tbody>
            {% set paginate = paginator.paginate() %}
            {% set authId = user.id %}
            {% set canEdit = user.authorise('user.edit') %}
            {% set canManageOwn = user.authorise('user.manageOwn') %}

            {% for item in paginate.getItems() %}
                {% set isSelf = authId == item.id %}
                {% set name = item.name ~ ' <small class="uk-text-meta">' ~ (item.role ? item.role.name : 'N/A') ~ '</small>' %}

                {% if item.is('super') %}
                    {% set name = name ~ '<span uk-icon="icon: bolt"></span>' %}
                {% endif %}

                {% set isCheckedIn = item.isCheckedIn() %}
                {% set avatar = item.registry('params').get('avatar') %}

                <tr{{ isSelf ? ' class="uk-text-bold"' : '' }}>
                    <td>
                        {% if isSelf AND !isCheckedIn %}
                            {{ icon('lock') }}
                        {% else %}
                            <input class="uk-checkbox" type="checkbox" name="cid[]" value="{{ item.id }}"/>
                        {% endif %}
                    </td>
                    <td>
                        <div class="uk-grid uk-grid-small">
                            {% if avatar %}
                                <img class="uk-preserve-width" src="{{ public(avatar) }}" width="55" alt=""/>
                            {% endif %}
                            {% if isCheckedIn %}
                                {{ partial('Grid/CheckedIn', ['item': item, 'title': name]) }}
                            {% else %}
                                {% if canEdit OR (canManageOwn AND isSelf) %}
                                    <a class="uk-link-reset" href="{{ uri.routeTo('/edit/' ~ item.id) }}">
                                        {{ name }}
                                    </a>
                                {% else %}
                                    {{ name }}
                                {% endif %}
                            {% endif %}
                        </div>
                    </td>
                    <td class="uk-text-nowrap">
                        {{ item.email }}
                    </td>
                    <td class="uk-text-nowrap">
                        {{ item.username }}
                    </td>
                    <td class="uk-text-nowrap">
                        {{ _(item.active === 'Y' ? 'active' : 'banned') }}
                    </td>
                    <td class="uk-text-nowrap">
                        {{ helper('Date::relative', item.createdAt) }}
                    </td>
                    <td class="uk-text-nowrap">
                        {{ item.id }}
                    </td>
                </tr>
            {% endfor %}
            </tbody>
        </table>
    </div>
    {{ partial('Pagination/Pagination') }}
    {{ csrfInput() }}
</form>