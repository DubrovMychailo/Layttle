import React from 'react';
import PropTypes from 'prop-types';
import './ChatMessage.css';

const ChatMessage = ({ sender, text, media, mediaType, avatar, isCurrentUser }) => {

    const avatarSrc = avatar || '/uploads/MiraclesPlay.png';

    return (
        <div className={`message ${isCurrentUser ? 'right' : 'left'}`}>
            {!isCurrentUser && <img src={avatarSrc} className="photo" alt="Avatar" />}
            <div className="message-content">
                <strong>{sender}:</strong>
                <div className="message-text">{text}</div>
                {media && mediaType === 'image' && (
                    <img src={media} className="content mt-1" alt="Image" />
                )}
                {media && mediaType === 'video' && (
                    <video controls className="mt-1">
                        <source src={media} type="video/mp4" />
                    </video>
                )}
            </div>
            {isCurrentUser && <img src={avatarSrc} className="photo" alt="Avatar" />}
        </div>
    );
};

ChatMessage.propTypes = {
    sender: PropTypes.string.isRequired,
    text: PropTypes.string,
    media: PropTypes.string,
    mediaType: PropTypes.oneOf(['image', 'video']),
    avatar: PropTypes.string,
    isCurrentUser: PropTypes.bool
};

/**
 * Компонент ChatInput відповідає за введення повідомлень.
 *
 * @component
 * @example
 * const handleSend = () => { console.log("Надіслано!"); }
 * return <ChatInput onSend={handleSend} />;
 */
const ChatInput = ({ onSend }) => {
    // логіка компонента
    return (
        <input type="text" onKeyPress={(e) => e.key === 'Enter' && onSend(e.target.value)} />
    );
};

export default ChatMessage;

