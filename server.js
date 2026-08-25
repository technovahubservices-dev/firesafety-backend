const express = require("express");

const app = express();

app.get("/", (req, res) => {
  res.status(200).send(`
    <html>
      <head>
        <meta charset="utf-8" />
        <meta name="viewport" content="width=device-width, initial-scale=1" />
        <title>Fire Safety Backend</title>
        <style>
          body {
            margin: 0;
            min-height: 100vh;
            display: grid;
            place-items: center;
            font-family: Arial, sans-serif;
            background: linear-gradient(135deg, #0f172a, #1d4ed8);
            color: #fff;
          }
          .card {
            padding: 32px 36px;
            border-radius: 16px;
            background: rgba(255, 255, 255, 0.1);
            backdrop-filter: blur(12px);
            box-shadow: 0 20px 60px rgba(0, 0, 0, 0.25);
            max-width: 560px;
          }
          h1 { margin: 0 0 12px; font-size: 28px; }
          p { margin: 0 0 8px; line-height: 1.5; }
          code {
            display: inline-block;
            padding: 2px 8px;
            border-radius: 8px;
            background: rgba(15, 23, 42, 0.45);
          }
        </style>
      </head>
      <body>
        <div class="card">
          <h1>Fire Safety Backend</h1>
          <p>Status: running</p>
          <p>Debug endpoint: <code>/debug-db-host</code></p>
          <p>Health check: <code>/ping</code></p>
        </div>
      </body>
    </html>
  `);
});

// Lightweight uptime endpoint for external pings to reduce Render cold starts.
app.get("/ping", (req, res) => {
  res.status(200).send("OK");
});

// Mirror the Laravel debug route so the deployed service can expose the same
// database host diagnostics even when Render boots this Node entrypoint.
app.get("/debug-db-host", (req, res) => {
  res.json({
    env_host: process.env.DB_HOST || null,
    config_host: process.env.DB_HOST || null,
    port: process.env.DB_PORT || null,
    note: "This endpoint is served by server.js. If you expect Laravel routes, check the deployment start command.",
  });
});

const PORT = process.env.PORT || 3000;

app.listen(PORT, () => {
  console.log(`Server running on port ${PORT}`);
});
