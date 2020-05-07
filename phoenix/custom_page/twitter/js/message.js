
$(function() {
    var EXP = 1000 * 60 * 60 * 24 * 10;

    if (get('user', EXP)) {
        $.ajax({
            type: 'get',
            url: baseURL + '/api/v2/system-notice/list',
            dataType: 'json',
            xhrFields: {
                withCredentials: true
            },
            headers: {
              "X-SessionId": localStorage.getItem("uc_sessionId")
            },
            success: function (data) {
                if (data.code === 0 && data.data.system_notice_list.lengh !== 0) {
                    $("#notice-list").html('')
                    data.data.system_notice_list.forEach(function(d){
                        var messageHtml =  '<div class="message">'
                        +'<div class="time">' + cutstr(d.created_at) + '</div>'
                        + '<div class="body">'
                        +  '<div class="text">' + d.content + '</div>'
                        +  '</div>'
                        +  '</div>'
                        $("#notice-list").append(messageHtml)
                        if (d.read === 0) {
                          $.ajax({
                              type: 'put',
                              url: baseURL + '/api/v2/system-notice/' + d.id + '/read',
                              xhrFields: {
                                  withCredentials: true
                              },
                              headers: {
                                "X-SessionId": localStorage.getItem("uc_sessionId")
                              },
                              success: function(data) {
                                  // console.log(data);
                              },
                              error: function(err) {
                                  console.log(err);
                              }
                          })
                        }
                    })
                }
            },
            error: function (xhr, textStatus, errorThrown) {
              if (xhr.status == 401) {
                goLoginPage();
              } else {
                // 调用外部的error
                error && error(xhr, textStatus, errorThrown);
              }
            }
        })
        
    } else {
        goLoginPage();
    }

    // saveLastUrl();
})
