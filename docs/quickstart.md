# Comments Work plugin for Craft CMS 3.x



## Quickstart with twig templates

Comments can be attached to any element, such as content elements or users.


Assuming you have an 'entry', you can add comment forms and lists as in the examples below.

### Add a comment form

Add the code below to your entry template. It renders a simple form with a title and comment field, and
'signs' the form so malicious users cannot submit comments to arbitrary content.

Bootstrap 4 example:

```twig
{# @var commentsWork \twentyfourhoursmedia\commentswork\services\CommentsWorkService #}
{% set commentsWork = craft.commentsWork.service %}

{% if not currentUser and not commentsWork.allowAnonymous(entry) %}
    <p>Anonymous commenting is not allowed. Please login.</p>
{% else %}

    <div class="card bg-light"><div class="card-header">
            Leave a comment
        </div>
        <div class="card-body">

    <form method="post" action="{{ url('/actions/comments-work/default/post-comment') }}">
        {{ csrfInput() }}
        {{ signCommentForm(entry) }}
        <input name="redirect" value="{{ craft.app.request.url }}#comments" type="hidden"/>
        <input name="elementId" value="{{ entry.id }}" type="hidden"/>
        <input name="siteId" value="{{ entry.siteId }}" type="hidden"/>
        <input name="commentFormat" value="text" type="hidden"/>

        <div class="form-group">
            <label for="comment-title">Title</label>
            <input name="title" id="comment-title" type="text" class="form-control" />
        </div>
        <div class="form-group">
            <label for="comment-content">Comment</label>
            <textarea name="comment" rows="5" id="comment-content" class="form-control"></textarea>
        </div>

        <div>
            <input type="submit" class="btn btn-primary" value="Post comment"/>
        </div>
    </form>
        </div>
    </div>
{% endif %}
```

### Displaying comments

The code below renders the last 10 comments on your page.

Bootstrap 4 example:

```twig
{# @var commentsWork \twentyfourhoursmedia\commentswork\services\CommentsWorkService #}
{% set commentsWork = craft.commentsWork.service %}

<div id="comments">
    <br/>
    <p>{{  commentsWork.countComments(entry) }} comments</p>

    {% set comments = commentsWork.fetchComments(entry, 0, 10) %}
    {% for comment in comments %}
        {# @var comment \twentyfourhoursmedia\commentswork\models\CommentModel #}
        <div class="card card bg-light">
            <div class="card-body">
                <div class="row">
                    <div class="col-md-2">
                        <img src="https://image.ibb.co/jw55Ex/def_face.jpg" class="img img-rounded img-fluid" width="64">
                        <p class="text-secondary text-center">{{ comment.dateCreated | date }} {{ comment.dateCreated | date('H:i') }}</p>
                    </div>
                    <div class="col-md-10">
                        {%- if comment.user %}<p><a href="https://maniruzzaman-akash.blogspot.com/p/contact.html"><strong>{{ comment.user.friendlyName }}</strong></a></p>{% endif -%}
                        {%- if comment.title is not empty %}
                            <p><strong>{{ comment.title }}</strong></p>
                        {% endif -%}
                        {%- if comment.comment is not empty %}
                            <p>
                                {{ comment | commentAsHtml }}
                            </p>
                        {% endif -%}


                    </div>
                </div>
            </div>
        </div>
        <br/>
    {% endfor %}
</div>
```

