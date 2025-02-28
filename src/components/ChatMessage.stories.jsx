import React from 'react';
import ChatMessage from './ChatMessage';

export default {
    title: 'Chat/ChatMessage',
    component: ChatMessage,
};

const Template = (args) => <ChatMessage {...args} />;

export const DefaultMessage = Template.bind({});
DefaultMessage.args = {
    sender: 'Дубров Михайло',
    text: 'Привіт, як справи?!',
    isCurrentUser: false,
};

export const ImageMessage = Template.bind({});
ImageMessage.args = {
    sender: 'Дубров Михайло',
    text: 'Оціни це фото :)',
    media: '/uploads/67a32d93c8c45.png',
    mediaType: 'image',
    avatar: '',
    isCurrentUser: true,
};

export const VideoMessage = Template.bind({});
VideoMessage.args = {
    sender: 'Дубров Михайло',
    text: 'Доволі смішне відео!',
    media: '/uploads/spangbob.mp4',
    mediaType: 'video',
    // avatar порожній — використовуватиметься дефолтна аватарка
    avatar: '',
    isCurrentUser: false,
};
