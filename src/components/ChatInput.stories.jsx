import React from 'react';
import ChatInput from './ChatInput';

export default {
    title: 'Chat/ChatInput',
    component: ChatInput,
};

const Template = (args) => <ChatInput {...args} />;

export const DefaultInput = Template.bind({});
DefaultInput.args = {
    onSendMessage: (message) => console.log('Message sent:', message),
};
