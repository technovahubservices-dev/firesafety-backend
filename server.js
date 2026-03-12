const express = require("express");

const app = express();

// Lightweight uptime endpoint for external pings to reduce Render cold starts.
app.get("/ping", (req, res) => {
  res.status(200).send("OK");
});

const PORT = process.env.PORT || 3000;

app.listen(PORT, () => {
  console.log(`Server running on port ${PORT}`);
});
