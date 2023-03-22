## Autom8

*Autom8* is a tool to help with the automation of some of the common events associated with GitHub Project (V2) boards via webhooks. When an event is fire, the *Autom8* system kicks in and, based on event you describe in natural language, can preform other tasks.

### Installation

#### Starting the Service with Docker
*Autom8* uses Docker containers to host the service, so firing it up is just like any other project. We've also provided a handy shell script to help out (note: requires both Docker and Docker Compose):

```
cd build
./setup.sh
```

#### Making it public
Then once the container comes up, you can point something like [Ngrok](https://ngrok.com/) at it to make it publicly accessible. It has to be public as one of the GitHub webhook has to be able to reach the endpoint.

#### Setting up the .env file

You'll notice that there's a `.env.example` file in the `bootstrap` directory. This provides you with an example of what values will need to be set to use the tool. To use it, however, you need to copy it over to a `.env` file and fill in the values.

> NOTE: the "log path" is on the local machine, so to view it, you'll need to go into the Docker container and tail the log from there.

#### Setting up the GitHub webhook

As a final step, you need to set up the webhook on the GitHub side. To do this, visit the Settings page for your organization (something like `https://github.com/organizations/<org name>/settings/hooks`) and create a new hook that points the "Payload URL" to your Ngrok address.

> NOTE: To only get Project (V2) related events, select "Let me select individual events" and then check the box for the projects related items. Some project board actions need more than just the V2 selections.

### Writing tests

The point of *Autom8* is to try and be accessible to everyone, not just developers. To that end, the tests ("events") are written in natural English and are handled as they're received, running through each test for a match.

For example, to make a comment every time an item is reordered in the project board list, you could use something like this:

```
When a project item is reordered
Then comment on the item "hi there!"
```

Then, every time an item is moved, it will receive a comment. These events are stored in the "events" directory of your choosing (as defined in the `.env` file).
