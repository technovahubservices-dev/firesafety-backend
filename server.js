const express = require("express");

const app = express();

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
