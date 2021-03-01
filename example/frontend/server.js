const express = require('express')
const app = express()
const port = 3000

app.get('/frontend', (req, res) => {
  res.send(`
    This is the frontend<br>
    <a href="/admin">Admin</a><br>
    <a href="/">Api</a>
  `)
})

app.listen(port, () => {
  console.log(`Frontend app listening at http://localhost:${port}`)
})
