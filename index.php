<?php
// 设置OpenAI API密钥
$OPENAI_API_KEY = "";

// 处理聊天请求
if (isset($_POST['message'])) {
    $message = $_POST['message'];
    // 对消息进行 HTML 编码，防止浏览器解析
    $message = htmlspecialchars($message, ENT_QUOTES, 'UTF-8');
    $response = get_ai_response($message, $OPENAI_API_KEY);
    echo $response;
    exit;
}

// 获取AI响应
function get_ai_response($message, $api_key) {
    $data = array(
        "model" => "gpt-3.5-turbo",
        "messages" => array(
            array(
                "role" => "user",
                "content" => $message
            )
        )
    );

    $data_string = json_encode($data);
    $ch = curl_init('https://api.openai.com/v1/chat/completions');
    curl_setopt($ch, CURLOPT_TIMEOUT, 60); // 设置超时时间
    curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "POST");
    curl_setopt($ch, CURLOPT_POSTFIELDS, $data_string);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_HTTPHEADER, array(
        'Authorization: Bearer ' . $api_key,
        'Content-Type: application/json',
        'Content-Length: ' . strlen($data_string)
    ));

    $result = curl_exec($ch);
    if ($result === false) {
        return json_encode(array(
            "message" => "超时，请重试！",
            "role" => "assistant"
        ));
    }
    curl_close($ch);
    $response = json_decode($result, true);

    // 获取响应消息的文本
    $text = $response['choices'][0]['message']['content'];

    // 对文本进行 HTML 编码
    $html = htmlspecialchars($text, ENT_QUOTES, 'UTF-8');

    // 分段处理编码后的文本
    $paragraphs = explode("\n", $html);
    $html = '';
    foreach ($paragraphs as $paragraph) {
        if (trim($paragraph) !== '') {
            $html .= '<p>' . trim($paragraph) . '</p>';
        }
    }

    // 将AI响应作为JSON格式返回
    return json_encode(array(
        "message" => $html,
        "role" => "assistant"
    ));
}
?>
<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <title>ChatGPT在线聊天</title>
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <script src="https://code.jquery.com/jquery-3.5.1.min.js"></script>
    <script>
        $(function() {
            // 显示加载动画
            function show_loading() {
                $('#loading').show();
            }

            // 隐藏加载动画
            function hide_loading() {
                $('#loading').hide();
            }

            // 发送聊天消息并获取响应
            function send_message(message) {
                show_loading(); // 显示加载动画
                $.post(window.location.href, {
                    message: message
                }, function(data) {
                    hide_loading(); // 隐藏加载动画
                    add_message(data.message, data.role);
                }, 'json');
            }

            // 将消息添加到聊天记录中
            function add_message(message, role) {
                var message_html = '<div class="message ' + role + '">' + message + '</div>';
                $('#messages').append(message_html);
                $('#input').val('');
                $('#messages').scrollTop($('#messages')[0].scrollHeight);
            }

            // 提交表单以发送消息
            $('#chat-form').submit(function(e) {
                e.preventDefault();
                var message = $('#input').val();
// 对消息进行 HTML 编码，防止浏览器解析
message = $('<div/>').text(message).html();
add_message(message, 'user');
send_message(message);

            });
        });
    </script>
    <style>
        * {
            box-sizing: border-box;
            margin: 0;
            padding: 0;
        }

        body {
            font-family: sans-serif;
            font-size: 16px;
            line-height: 1.4;
            background-color: #f1f1f1;
        }

        #messages {
            display: flex;
            flex-direction: column;
            align-items: flex-start;
            margin: 10px;
            padding: 10px;
            background-color: #fff;
            border-radius: 10px;
            box-shadow: 0px 2px 10px rgba(0, 0, 0, 0.1);
            max-width: 90%;
            overflow-y: auto;
            height: 85vh;
            scrollbar-width: thin;
        }

        .message {
            margin: 5px;
            padding: 10px;
            border-radius: 10px;
            max-width: 70%;
            word-wrap: break-word;
        }

        .user {
            background-color: #DCF8C6;
            align-self: flex-end;
        }

        .assistant {
            background-color: #E5E5EA;
            align-self: flex-start;
        }

        .message.user:last-child {
            border-bottom-right-radius: 0;
        }

        .message.user:first-child {
            border-top-right-radius: 0;
        }

        .message.assistant:last-child {
            border-bottom-left-radius: 0;
        }

        .message.assistant:first-child {
            border-top-left-radius: 0;
        }

        .spinner {
            width: 40px;
            height: 40px;
            margin: 0 auto;
            border-radius: 50%;
            border-top: 5px solid #f8f8f8;
            border-right: 5px solid #f8f8f8;
           border-bottom: 5px solid #f8f8f8;
border-left: 5px solid #ccc;
animation: spin 1s infinite linear;
}
    @keyframes spin {
        0% { transform: rotate(0deg); }
        100% { transform: rotate(360deg); }
    }

    #input {
        width: 80%;
        margin: 10px;
        padding: 10px;
        border: none;
        background-color: #fff;
        border-radius: 30px;
        font-size: 16px;
        line-height: 1.4;
        box-shadow: 0px 2px 10px rgba(0, 0, 0, 0.1);
        outline: none;
    }

    #submit {
        margin: 10px;
        padding: 10px;
        background-color: #4EB6FF;
        color: #fff;
        border: none;
        border-radius: 30px;
        cursor: pointer;
        font-size: 16px;
        line-height: 1.4;
        outline: none;
    }

    #submit:hover {
        background-color: #3A91CC;
    }

    /* 优化移动端CSS */
    @media only screen and (max-width: 600px) {
        #messages {
            max-width: 100%;
            margin: 5px;
        }

        .message {
            margin: 5px;
            max-width: 80%;
        }

        #input {
            width: 70%;
            margin: 10px 5px;
            padding: 10px;
            font-size: 16px;
            line-height: 1.4;
        }

        #submit {
            margin: 10px 5px;
            padding: 10px;
            font-size: 16px;
            line-height: 1.4;
        }
    }
</style>
</head>
<body>
<!-- 加载动画 -->
<div id="loading" style="display: none; text-align: center;">
    <div class="spinner"></div>
</div>
<div id="messages"></div>
<form id="chat-form">
    <input type="text" id="input" placeholder="输入消息" autocomplete="off">
    <input type="submit" id="submit" value="发送">
</form>
</body>
</html>
