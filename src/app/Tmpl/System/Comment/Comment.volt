{% do addAssets(['css/comment.css', 'js/comment.js']) %}
{% set commentInstance = helper('Comment::getInstance', ucmItem.context, ucmItem.id, offset) %}
{%- macro commentHeader(item) %}
    <header class="uk-comment-header">
        <div class="uk-grid-medium uk-flex-middle" uk-grid>
            <div class="uk-width-auto">
                {% set author = item.getAuthor() %}
                {% if author %}
                    {% set avatar = helper('Image::loadImage', author.getParams().get('avatar')) %}
                    {% if (avatar) %}
                        <img class="uk-comment-avatar uk-border-circle"
                             src="{{ avatar.getResize(32, 32) }}"
                             width="40"
                             height="40" alt="{{ item.userName | escape_attr }}"/>
                    {% else %}
                        <div class="c-avatar">{{ substr(item.userName, 0, 1) }}</div>
                    {% endif %}
                {% else %}
                    <div class="c-avatar">{{ substr(item.userName, 0, 1) }}</div>
                {% endif %}
            </div>
            <div class="uk-width-expand">
                <h4 class="uk-comment-title uk-margin-remove">
                    <a class="uk-link-reset" href="#">{{ item.userName }}</a>
                </h4>
                <ul class="uk-comment-meta uk-subnav uk-subnav-divider uk-margin-remove-top">
                    <li>
                        <span>{{ helper('Date::relative', item.createdAt) }}</span>
                    </li>
                    <li>
                        <a class="uk-link-muted" data-target-author="{{ item.userName | escape_attr }}">
                            {{ _('reply') }}
                        </a>
                    </li>
                </ul>
            </div>
        </div>
    </header>
{% endmacro %}
<h3 class="uk-margin-medium-bottom">{{ _s('comments-count', commentInstance.totalItems) }}</h3>
<div class="comments-container" id="comments-container-{{ ucmItem.id }}">
    <ul class="uk-comment-list">
        {% for item in commentInstance.items %}
            <li data-comment-id="{{ item.id }}">
                <article class="uk-comment{{ user.id == item.createdBy AND user.id > 0 ? ' uk-comment-primary' : '' }}">
                    {{ commentHeader(item) }}
                    <div class="uk-comment-body">
                        <p>{{ item.userComment }}</p>
                    </div>
                </article>
                {% set replies = item.replies %}
                {% if replies.count() %}
                    <ul>
                        {% for reply in replies %}
                            <li>
                                <article
                                        class="uk-comment{{ reply.createdBy == item.createdBy AND reply.createdBy > 0 ? ' uk-comment-primary' : '' }}">
                                    {{ commentHeader(reply) }}
                                    <div class="uk-comment-body">
                                        <p>{{ item.userComment }}</p>
                                    </div>
                                </article>
                            </li>
                        {% endfor %}
                    </ul>
                {% endif %}
            </li>
        {% endfor %}
    </ul>
</div>