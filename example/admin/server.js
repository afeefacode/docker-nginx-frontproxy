const express = require('express')
const app = express()
const port = 3000

app.get('/admin', (req, res) => {
  res.send(`
    This is the admin<br>
    <a href="/frontend">Frontend</a><br>
    <a href="/">Api</a>
  `)
})

app.listen(port, () => {
  console.log(`Admin app listening at http://localhost:${port}`)
})
