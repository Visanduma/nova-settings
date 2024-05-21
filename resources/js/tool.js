import Test from './pages/Test'

Nova.booting((app, store) => {
    Nova.inertia('Test', Test)
})
