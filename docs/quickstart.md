# Comments Work plugin for Craft CMS 3.x



## Quickstart with twig templates

Comments can be attached to any element, such as content elements or users.


Assuming you have an 'entry', you can add comment forms and lists as in the examples below.

### Add a comment form

Add the code below to your entry template. It renders a simple form with a title and comment field, and
'signs' the form so malicious users cannot submit comments to arbitrary content.


```twig

{# @var commentsWork \twentyfourhoursmedia\commentswork\services\CommentsWorkService #}
{% set commentsWork = craft.commentsWork.service %}

{% if not currentUser and not commentsWork.allowAnonymous(entry) %}
    <p>Anonymous commenting is not allowed. Please login.</p>
{% else %}
    <form method="post" action="{{ url('/actions/comments-work/default/post-comment') }}">
        {{ csrfInput() }}
        {{ signCommentForm(entry) }}
        <input name="redirect" value="{{ craft.app.request.url }}#comments" type="hidden"/>
        <input name="elementId" value="{{ entry.id }}" type="hidden"/>
        <input name="commentFormat" value="text" type="hidden"/>

        <h3><label for="comment-title">Title</label></h3>
        <input name="title" id="comment-title"/>

        <h3><label for="comment-content">Comment</label></h3>
        <textarea name="comment" rows="5" id="comment-content"></textarea>

        <div>
            <input type="submit"/>
        </div>
    </form>
{% endif %}

```

### Displaying comments

The code below renders the last 10 comments on your page

```twig
{# @var commentsWork \twentyfourhoursmedia\commentswork\services\CommentsWorkService #}
{% set commentsWork = craft.commentsWork.service %}

<div id="comments">
    <p>{{  commentsWork.countComments(entry) }} comments</p>
    
    {% set comments = commentsWork.fetchComments(entry, 0, 10) %}
    {% for comment in comments %}
        {# @var comment \twentyfourhoursmedia\commentswork\models\CommentModel #}
        <div class="comment">
            {%- if comment.title is not empty %}
                <h3 class="comment-title">{{ comment.title }}</h3>
            {% endif -%}
            {%- if comment.comment is not empty %}
                <div class="comment-content">
                {{ comment | commentAsHtml }}
                </div>
            {% endif -%}
            <div>
                <small>
                {%- if comment.user %}
                    {{ comment.user.friendlyName }},
                {% endif -%}
        
                {{ comment.dateCreated | date }} {{ comment.dateCreated | date('H:i') }}
                </small>
            </div>
        </div>
    {% endfor %}
</div>
```

