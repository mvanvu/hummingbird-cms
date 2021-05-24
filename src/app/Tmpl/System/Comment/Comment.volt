{% set params = ucmItem.getParams() %}

{% if params.get('allowUserComment') === 'Y' %}
    {% set offset = offset is defined ? offset : 0 %}
    {% set commentInstance = helper('Comment::getInstance', ucmItem.context, ucmItem.id, offset) %}

    {% if params.get('commentWithEmoji') === 'Y' %}
        {% do addAssets(['css/comment.css', 'js/emoji.js', 'js/comment.js']) %}
    {% else %}
        {% do addAssets(['css/comment.css', 'js/comment.js']) %}
    {% endif %}

    {%- macro commentHeader(item, parentId) %}
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
                            <a class="uk-link-muted reply" data-parent-id="{{ parentId }}"
                               data-author="{{ item.userName | escape_attr }}">
                                {{ _('reply') }}
                            </a>
                        </li>
                    </ul>
                </div>
            </div>
        </header>
    {% endmacro %}
    {% set itemsCount = count(commentInstance.items) %}
    <div class="comments-container" id="comments-container{{ ucmItem.id }}" data-item-id="{{ ucmItem.id }}"
         data-context="{{ ucmItem.context }}" data-total="{{ commentInstance.totalItems }}"
         data-offset="{{ itemsCount + 1 }}">
        <h3 class="uk-margin-medium-bottom comments-count">{{ _s('comments-count', commentInstance.totalItems) }}</h3>
        <ul class="comments-list uk-comment-list">
            {% for item in commentInstance.items %}
                <li data-comment-id="{{ item.id }}">
                    <article
                            class="uk-comment{{ user.id == item.createdBy AND user.id > 0 ? ' uk-comment-primary' : '' }}">
                        {{ commentHeader(item, item.id) }}
                        <div class="uk-comment-body">
                            <p>{{ item.userComment }}</p>
                        </div>
                    </article>
                    {% set replies = item.replies %}
                    {% set repliesCount = replies.count() %}
                    {% if repliesCount > 0 %}
                        <a class="uk-link-text uk-text-meta uk-margin show-replies">
                            <span uk-icon="icon: forward"></span>
                            {{ _s('replies-num', repliesCount) }}
                        </a>
                        <ul class="uk-hidden">
                            {% for reply in replies %}
                                <li>
                                    <article
                                            class="uk-comment{{ reply.createdBy == item.createdBy AND reply.createdBy > 0 ? ' uk-comment-primary' : '' }}">
                                        {{ commentHeader(reply, item.id) }}
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

            {% if commentInstance.totalLines > itemsCount %}
                <li>
                    <a class="uk-button uk-button-default uk-background-muted uk-width-1-1 view-more">
                        {{ _('view-more') }}
                    </a>
                </li>
            {% endif %}
        </ul>
        <p class="uk-text-right">
            <a class="uk-link-text show-modal">
                {{ _('write-comment-for-this-post') }}
            </a>
        </p>
        <div id="modal-form{{ ucmItem.id }}" uk-modal>
            <div class="uk-modal-dialog">
                {% if params.get('commentAsGuest') != 'Y' AND user.is('guest') %}
                    <div class="uk-modal-header uk-background-muted">
                        <div class="uk-modal-title">
                            {{ _('login-to-post-comment') }}
                        </div>
                    </div>
                    <div class="uk-modal-body">
                        {{ helper('Widget::createWidget', 'Login', [], true) }}
                    </div>
                {% else %}
                    <div class="comments-form uk-modal-body" id="comment-form{{ ucmItem.id }}">
                        {% if user.is('guest') %}
                            <div class="uk-grid-small uk-margin uk-child-width-1-2@s" uk-grid>
                                <div>
                                    <input class="uk-input" name="userName" type="text" autocomplete="off"
                                           placeholder="{{ _('enter-your-name') | escape_attr }}"/>
                                </div>
                                <div>
                                    <input class="uk-input" name="userEmail" type="email" autocomplete="off"
                                           placeholder="{{ _('enter-your-email-address') | escape_attr }}"/>
                                </div>
                            </div>
                        {% endif %}
                        <div class="uk-margin">
                    <textarea class="uk-textarea input-emoji" name="userComment" rows="2"
                              cols="15" autocomplete="off"></textarea>
                        </div>
                        <div class="uk-margin uk-flex uk-flex-right">
                            <a class="post-comment uk-button uk-button-primary">
                                {{ _('post-your-comment') }}
                            </a>
                            <a class="uk-button uk-button-danger uk-margin-small-left uk-modal-close">
                                {{ _('close') }}
                            </a>
                        </div>
                    </div>
                {% endif %}
            </div>
        </div>
    </div>
{% endif %}