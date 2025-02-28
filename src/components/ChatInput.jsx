import React, { useState } from 'react';
import PropTypes from 'prop-types';
import './ChatInput.css';

const ChatInput = ({ onSendMessage }) => {
    const [message, setMessage] = useState('');
    const [file, setFile] = useState(null);

    const handleSubmit = (e) => {
        e.preventDefault();
        if (message.trim() || file) {
            onSendMessage({ text: message, file });
            setMessage('');
            setFile(null);
        }
    };

    return (
        <form id="chat-form" onSubmit={handleSubmit}>
            <label htmlFor="media" className="file-label">
                📎
            </label>
            <input type="file" id="media" onChange={(e) => setFile(e.target.files[0])} />

            <input
                type="text"
                value={message}
                onChange={(e) => setMessage(e.target.value)}
                placeholder="Напишіть повідомлення..."
            />

            <button type="submit">📩</button>
        </form>
    );
};

ChatInput.propTypes = {
    onSendMessage: PropTypes.func.isRequired
};

export default ChatInput;
