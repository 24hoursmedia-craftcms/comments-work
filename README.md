# Comments Work plugin for Craft CMS 3.x

An easy to use and straightforward commenting plugin for Craft CMS 3.x.
Allow users to post comments to your content, and moderate them from the dashboard.

## Documentation

Read all at our [online documentation resource](https://io.24hoursmedia.com/comments-work) 

## Comments Work Overview

- Add user comments to any type of element, such as users and content items
- Get a nice overview of submitted comments in the CP
- Moderation: delete, approve or mark as spam in the CP
- Moderation: modify comment contents in the CP
- Comment forms are signed so malicious users cannot submit comments to arbitrary content

## Usage

Quick example how code would look on the front-end. Read more at:
* https://io.24hoursmedia.com/comments-work/show-comment-entries-on-a-page
* https://io.24hoursmedia.com/comments-work/form

```
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

## Requirements

This plugin requires Craft CMS 3.0 or later, and works best with the PRO edition.
(Other editions do not support users)

## Installation

To install the plugin, follow the instructions at
https://io.24hoursmedia.com/comments-work/installation

## Configuring Comments Work

There are two configuration options. In the Admin CP, go to 'Settings' -> 'Comments Work'.
Read more at https://io.24hoursmedia.com/comments-work/configuration


- **'auto approve comments'** - if enabled, comments are shown immediately on the site.This bypasses the moderation. NOT RECOMMENDED WHEN ANONYMOUS COMMENTS ARE ENABLED!
- **'Allow anonymous comments'** - allows anonymous users to post comments.





---
Brought to you by [24hoursmedia](https://www.24hoursmedia.com)
