$(function(){
    
    $.ajaxSetup({ cache: true });
    $.getScript('//connect.facebook.net/en_UK/all.js', function(){
        FB.init({
            appId: '754718704577151',
            xfbml: true,
            status: true
        });

        var fbPosts = [];

        function FbPost(data) {
            this.id = data.id;
            this.title = data.title;
            this.date = data.date;
            this.init(data);
        }

        FbPost.prototype = {
            'message': null,
            'picture': null,
            'link': [],
            'hasPic': false,
            'hasMessage': false,
            'init': function(data) {
                var title = '<h3>' + this.title + '<span class="pull-right">' + this.date + '</span></h3>';
                var picture = '';
                var message = '';
                if (typeof data.picture != 'undefined') {
                    this.picture = data.picture;
                    this.hasPic = true;
                    if (typeof data.message != 'undefined') {
                        this.message = data.message;
                        this.hasMessage = true;
                        message = '<div class="col-sm-' + ((this.hasPic)? '7' : '12') + ' pull-right">';
                        message = message + '<p>' + this.message + '</p></div>';
                    }
                    img = '<img class="post-picture" src="' + this.picture + '">';
                    picture = '<div class="col-sm-' + ((this.hasMessage)? '5' : '9  col-sm-offset-1') + '">' + img + '</div>';
                }
                else{
                    if (typeof data.message != 'undefined') {
                        this.message = data.message;
                        message = '<div class="col-sm-' + ((this.hasPic)? '7 pull-right' : '12') + ' clearfix">';
                        message = message + '<p class="message">' + this.message + '</p></div>';
                    }
                }
                var link = '';
                if (typeof data.link != 'undefined' && typeof data.link.name != 'undefined') {
                    this.link = data.link;
                    var linkPicture = '';
                    var linkName = '<h4>' + this.link.name + '</h4>';
                    var linkDescription = '';
                    if (typeof this.link.picture != 'undefined') {
                        var img = '<img src="' + this.link.picture + '">';
                        linkPicture = '<div class="col-sm-4">' + img + '</div>';
                    }
                    if (typeof this.link.description != 'undefined') {
                        var description =
                        linkDescription = '<p class="description">' +
                        this.link.description.substr(0,110) +
                        ((this.link.description.length > 110)? ' ...' : '') +
                        '</p>';
                    }
                    var linkInfo = '<div class="col-sm-' + ((linkPicture != '')? '8' : '12') + '">' +
                            linkName + linkDescription +
                            '</div>';
                    link = '<div class="link clearfix">' +
                            '<a href="' + this.link.url + '" target="_blank" class="well clearfix">' + linkPicture + linkInfo + '</a>' +
                            '</div>';
                }
                var comments = '';
                $.each(data.comments, function() {
                    console.log(this);
                    var comment = '<div class="media">' +
                    '        <div class="media-left">' +
                    '<img class="media-object" src="' + this.user.picture + '" alt="Profile picture">' +
                    '</div>' +
                    '<div class="media-body">' +
                    '<h4 class="media-heading">' + this.user.profile + '</h4>' +
                                    '<p>' + this.message + '</p>' +
                    '<div class="media-bottom">' + this.likes + '</div>' +
                    '</div>' +
                    '</div>';
                    comments = comments + comment;
                });
                var html = '<article class="facebook-post container-fluid" data-fb-id="' + this.id + '">' + title +
                        '<div class="col-sm-7">' + message + picture + link + '</div>' +
                        '<div class="col-sm-5">' +
                        '<div class="well"><p>' + ((data.likes != '')? data.likes :'Be the first one to Rock this') + '</p>' + comments + '</div>' +
                        '</div>' +
                        '</article>';
                $('#facebookfeed').append(html);
            }
        };
        
        function getFacebookPost(post_ids, post_index) {
            $.ajax({
                'type': 'GET',
                'url': '/ajax/get/facebookpost',
                'dataType': 'json',
                'data': {'post_id': post_ids[post_index].id},
                'success': function(data) {
                    fbPosts[this.id] = new FbPost(data);
                    post_index++;
                    if (post_index < post_ids.length) {
                        getFacebookPost(post_ids, post_index);
                    }
                }
            });            
        }    
        
        FB.api(
            "/dangerlivevoltage/feed?fields=id",
            function (response) {
                if (response && !response.error) {
                    getFacebookPost(response.data, 0);
                }
            }
        );
        
    });
});