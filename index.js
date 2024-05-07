const { Client, GatewayIntentBits } = require('discord.js');
const bodyParser = require('body-parser');
const express = require('express');
const cors = require('cors');
const app = express();
const app2 = express();

const port1 = 3001; // API Server Port
const port2 = 3000; // Web Server Port

app.use(cors())
app2.use(express.static('src'));
app2.use(bodyParser.urlencoded({ extended: true }));

app2.get('/', (req, res) => {});

// チャンネルIDとそれに対応するURLを定義
const channelURLMap = {
  'YOUR_DISCORD_WEBHOOK_CHANNEL_ID': 'YOUR_DISCORD_WEBHOOK_URL',
  'YOUR_DISCORD_WEBHOOK_CHANNEL_ID': 'YOUR_DISCORD_WEBHOOK_URL',
};

app2.post('/', (req, res) => {
  const postchannelID = req.body.channelid;
  const postMessage = req.body.content;
  const postName = req.body.name;
  const posticonimgLink = req.body.image;

  // チャンネルIDに対応するURLを取得
  const targetUrl = channelURLMap[postchannelID];
  console.log(targetUrl);
  if (!targetUrl) {
    return res.status(400).send('Target URL not found for the provided channel ID');
  }

  fetch(targetUrl, {
    method: 'POST',
    headers: {
      'Content-Type': 'application/json',
    },
    body: JSON.stringify({
      content: postMessage,
      username: postName,
      avatar_url: posticonimgLink
    }),
  })
    .then(response => {
      if (!response.ok) {
        throw new Error('Failed to POST data to the target URL');
      }
      return response.json();
    })
    .then(data => {
      console.log('Data successfully posted:', data);
      res.sendStatus(200);
    })
    .catch(error => {
      console.error('Error:', error);
      res.redirect('http://localhost:3000');
    });
});

const client = new Client({
  intents: [
    GatewayIntentBits.Guilds,
    GatewayIntentBits.GuildMessages,
    GatewayIntentBits.GuildMembers,
    GatewayIntentBits.MessageContent,
  ],
});

const targetServerId = 'YOUR_TARGET_SERVER_ID';

app.get('/:channelId', async (req, res) => {
  const channelId = req.params.channelId;

  try {
    const messages = await fetchMessages(channelId);
    res.json(messages);
  } catch (error) {
    res.status(500).json({ error: error.message });
  }
});

async function fetchMessages(channelId) {
  const targetGuild = await client.guilds.fetch(targetServerId);
  const targetChannel = targetGuild.channels.cache.get(channelId);

  if (!targetChannel) {
    throw new Error('Target channel not found.');
  }

  const messages = await targetChannel.messages.fetch({ limit: 45 });

  return messages.map(message => {
    const imageUrl = message.attachments.first()?.url;
    const authorAvatarUrl = message.author.displayAvatarURL({ format: 'png', dynamic: true, size: 64 });

    return {
      messageId: message.id,
      author: {
        id: message.author.id,
        username: message.author.displayName,
        avatarUrl: authorAvatarUrl,
      },
      content: message.content,
      imageUrl: imageUrl,
      timestamp: message.createdTimestamp,
    };
  });
}

app.listen(port1, () => {
  console.log(`API Server is running at http://localhost:${port1}`);
});

app2.listen(port2, () => {
  console.log(`Web Server is running at http://localhost:${port2}`);
});

client.login('YOUR_DISCORD_BOT_TOKEN');

