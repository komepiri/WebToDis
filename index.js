const { Client, GatewayIntentBits } = require('discord.js');
const bodyParser = require('body-parser')
const express = require('express');
const cors = require('cors');

const app = express();
const app2 = express();

const port1 = 3001;
const port2 = 3000;

const WebhookURLMap = {
  'channelID1': 'http://example.com/webhook1',
  'channelID2': 'http://example.com/webhook2',
};

app2.use(express.static('src'));
app2.use(bodyParser.urlencoded({ extended: true }))
app.use(cors());

app2.get('/', (req, res) => {
});

app2.post('/', (req, res) => {
  const postchannelID = req.body.channelid;
  const postMessage = req.body.message;
  const postName = req.body.name;
  const postIconimgLink = req.body.imglink;

  // チャンネルIDに対応するURLを取得
  const targetUrl = WebhookURLMap[postchannelID];

  if (!targetUrl) {
    return res.status(400).send('Target URL not found for the provided channel ID');
  }

  fetch(targetUrl, {
    method: 'POST',
    headers: {
      'Content-Type': 'application/json',
    },
    body: JSON.stringify({
      username: postName,
      avatar_url: postIconimgLink,
      content: postMessage
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
      res.status(500).send('Internal Server Error');
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
let messagesCache = {};

app.get('/:channelId', (req, res) => {
  const channelId = req.params.channelId;

  if (!messagesCache[channelId]) {
    fetchMessages(channelId)
      .then(messages => {
        messagesCache[channelId] = messages;
        res.json(messages);
      })
      .catch(error => {
        res.status(500).json({ error: error.message });
      });
  } else {
    res.json(messagesCache[channelId]);
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